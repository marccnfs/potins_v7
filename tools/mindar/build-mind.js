// toolsOld/mindar/build-mind.js
// Usage: node build-mind.js /path/image.jpg /path/out.mind
import fs from 'fs';
import { createCanvas, loadImage } from 'canvas';  // si besoin
import { Compiler } from 'mind-ar/dist/mindar-image-compiler.js'; // chemin à vérifier

const [, , inPath, outPath] = process.argv;

async function run(){
    const img = await loadImage(inPath);
    // selon l’API, il faut fournir ImageData ou bitmap; adapte avec canvas si nécessaire
    const canvas = createCanvas(img.width, img.height);
    const ctx = canvas.getContext('2d');
    ctx.drawImage(img, 0, 0);
    const imageData = ctx.getImageData(0, 0, img.width, img.height);

    const compiler = new Compiler();
    const result = await compiler.compileImage(imageData); // ou compileImages([imageData,...])
    fs.writeFileSync(outPath, Buffer.from(result.mindData));
}
run();
