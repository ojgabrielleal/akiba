import fs from "node:fs";
import path from "node:path";

const root = path.resolve("docs/planejamento");
const requiredFrontmatterKeys = ["status", "tipo", "atualizado_em"];

function walk(directory) {
    return fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
        const fullPath = path.join(directory, entry.name);

        if (entry.isDirectory()) {
            return walk(fullPath);
        }

        return entry.isFile() && entry.name.endsWith(".md") ? [fullPath] : [];
    });
}

function noteNames(files) {
    const names = new Map();

    for (const file of files) {
        const relative = path.relative(root, file).replaceAll(path.sep, "/");
        const withoutExtension = relative.replace(/\.md$/, "");
        const basename = path.basename(withoutExtension);

        names.set(withoutExtension, file);
        names.set(basename, file);
    }

    return names;
}

function frontmatter(content) {
    const match = content.match(/^---\n([\s\S]*?)\n---/);

    if (!match) {
        return null;
    }

    return match[1];
}

function internalLinks(content) {
    return [...content.matchAll(/\[\[([^\]|#]+)(?:[|#][^\]]*)?\]\]/g)]
        .map((match) => match[1].trim());
}

const files = fs.existsSync(root) ? walk(root).sort() : [];
const names = noteNames(files);
const issues = [];

for (const file of files) {
    const relative = path.relative(root, file).replaceAll(path.sep, "/");
    const content = fs.readFileSync(file, "utf8");
    const metadata = frontmatter(content);

    if (!metadata) {
        issues.push(`${relative}: frontmatter ausente`);
    } else {
        for (const key of requiredFrontmatterKeys) {
            if (!new RegExp(`^${key}:`, "m").test(metadata)) {
                issues.push(`${relative}: frontmatter sem "${key}"`);
            }
        }
    }

    if (!/^#\s+.+/m.test(content)) {
        issues.push(`${relative}: titulo H1 ausente`);
    }

    for (const link of internalLinks(content)) {
        if (!names.has(link)) {
            issues.push(`${relative}: link interno sem nota encontrada: [[${link}]]`);
        }
    }
}

console.log(`Notas auditadas: ${files.length}`);

if (issues.length > 0) {
    console.log(`Problemas encontrados: ${issues.length}`);
    for (const issue of issues) {
        console.log(`- ${issue}`);
    }
    process.exitCode = 1;
} else {
    console.log("Nenhum problema encontrado.");
}

