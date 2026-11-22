import fs from "node:fs";
const f = "src/data/campus.js";
let s = fs.readFileSync(f, "utf8");
const antes = s.length;

// 1) categoría "Parques y áreas verdes"
s = s.replace(/^[ \t]*parque:.*\r?\n/m, "");
// 2) icono de areas-verdes
s = s.replace(/\s*"areas-verdes":\s*"[^"]*",?/, "");
// 3) el edificio y el paso del tour (ambos con id "areas-verdes")
s = s.replace(/\r?\n  \{\r?\n    id: "areas-verdes",[\s\S]*?\r?\n  \},/g, "");

fs.writeFileSync(f, s, "utf8");
console.log("Listo. Caracteres quitados:", antes - s.length);
