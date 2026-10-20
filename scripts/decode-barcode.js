#!/usr/bin/env node
/**
 * Decode 1D/QR barcode from image. Prints first code text to stdout.
 * Usage: node scripts/decode-barcode.js /path/to/image.jpg
 */
const path = require('path');
const imgPath = process.argv[2];
if (!imgPath) {
  process.stderr.write('usage: decode-barcode.js <image>\n');
  process.exit(2);
}

async function main() {
  let Jimp;
  let zxing;
  try {
    Jimp = require('jimp');
    zxing = require('@zxing/library');
  } catch (e) {
    process.stderr.write('deps missing\n');
    process.exit(3);
  }

  const {
    MultiFormatReader, BarcodeFormat, DecodeHintType,
    RGBLuminanceSource, BinaryBitmap, HybridBinarizer,
  } = zxing;

  function tryDecode(image) {
    const { width, height, data } = image.bitmap;
    const luminances = new Uint8ClampedArray(width * height);
    for (let i = 0; i < width * height; i++) luminances[i] = data[i * 4];
    const hints = new Map();
    hints.set(DecodeHintType.POSSIBLE_FORMATS, [
      BarcodeFormat.CODE_128, BarcodeFormat.CODE_39, BarcodeFormat.EAN_13,
      BarcodeFormat.EAN_8, BarcodeFormat.ITF, BarcodeFormat.UPC_A, BarcodeFormat.QR_CODE,
    ]);
    hints.set(DecodeHintType.TRY_HARDER, true);
    const reader = new MultiFormatReader();
    reader.setHints(hints);
    return reader.decode(new BinaryBitmap(new HybridBinarizer(new RGBLuminanceSource(luminances, width, height))));
  }

  const full = await Jimp.read(path.resolve(imgPath));
  const h = full.bitmap.height;
  const w = full.bitmap.width;
  const crops = [
    full.clone().crop(0, Math.floor(h * 0.5), w, Math.floor(h * 0.5)),
    full.clone().crop(0, Math.floor(h * 0.6), w, Math.floor(h * 0.4)),
    full.clone(),
  ];

  for (const crop of crops) {
    const variants = [
      crop.clone().greyscale().contrast(0.5),
      crop.clone().greyscale().contrast(0.8).normalize(),
      crop.clone().greyscale().invert().contrast(0.4),
      crop.clone().greyscale().scale(2).contrast(0.5),
    ];
    for (const v of variants) {
      try {
        const r = tryDecode(v);
        process.stdout.write(String(r.getText()).trim() + '\n');
        process.exit(0);
      } catch (_) {}
    }
  }
  process.exit(1);
}

main().catch(() => process.exit(1));
