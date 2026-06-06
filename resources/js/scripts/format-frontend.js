import { readdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';

const projectRoot = process.cwd();
const svelteRoot = path.join(projectRoot, 'resources', 'js');

async function findSvelteFiles(directory) {
    const entries = await readdir(directory, { withFileTypes: true });
    const files = [];

    for (const entry of entries) {
        const fullPath = path.join(directory, entry.name);

        if (entry.isDirectory()) {
            files.push(...await findSvelteFiles(fullPath));
            continue;
        }

        if (entry.isFile() && entry.name.endsWith('.svelte')) {
            files.push(fullPath);
        }
    }

    return files;
}

function getAttributes(attributes) {
    const normalized = attributes.trim();
    const parsed = [];
    let current = '';
    let quote = null;
    let braceDepth = 0;
    let templateExpressionDepth = 0;

    for (let index = 0; index < normalized.length; index += 1) {
        const character = normalized[index];
        const nextCharacter = normalized[index + 1];

        if (quote) {
            if (quote === '`' && character === '$' && nextCharacter === '{') {
                templateExpressionDepth += 1;
                current += '${';
                index += 1;
                continue;
            }

            if (quote === '`' && character === '}' && templateExpressionDepth > 0) {
                templateExpressionDepth -= 1;
                current += character;
                continue;
            }

            current += character;

            if (character === quote && templateExpressionDepth === 0) {
                quote = null;
            }

            continue;
        }

        if (character === '"' || character === "'" || character === '`') {
            quote = character;
            current += character;
            continue;
        }

        if (character === '{') {
            braceDepth += 1;
            current += character;
            continue;
        }

        if (character === '}') {
            braceDepth -= 1;
            current += character;
            continue;
        }

        if (/\s/.test(character) && braceDepth === 0) {
            if (current) {
                parsed.push(current);
                current = '';
            }

            continue;
        }

        current += character;
    }

    if (current) {
        parsed.push(current);
    }

    return parsed;
}

function hasBalancedAttributeSyntax(attributes) {
    let quote = null;
    let braceDepth = 0;
    let templateExpressionDepth = 0;

    for (let index = 0; index < attributes.length; index += 1) {
        const character = attributes[index];
        const nextCharacter = attributes[index + 1];

        if (quote) {
            if (quote === '`' && character === '$' && nextCharacter === '{') {
                templateExpressionDepth += 1;
                index += 1;
                continue;
            }

            if (quote === '`' && character === '}' && templateExpressionDepth > 0) {
                templateExpressionDepth -= 1;
                continue;
            }

            if (character === quote && attributes[index - 1] !== '\\' && templateExpressionDepth === 0) {
                quote = null;
            }

            continue;
        }

        if (character === '"' || character === "'" || character === '`') {
            quote = character;
            continue;
        }

        if (character === '{') {
            braceDepth += 1;
            continue;
        }

        if (character === '}') {
            braceDepth -= 1;
        }
    }

    return quote === null && braceDepth === 0;
}

function repairSplitBracedAttributes(source) {
    const lines = source.split(/\r?\n/);

    for (let index = 1; index < lines.length; index += 1) {
        if (lines[index].trim() !== '}') {
            continue;
        }

        const openingBraces = (lines[index - 1].match(/\{/g) ?? []).length;
        const closingBraces = (lines[index - 1].match(/\}/g) ?? []).length;

        if (openingBraces > closingBraces) {
            lines[index - 1] = `${lines[index - 1]}}`;
            lines.splice(index, 1);
            index -= 1;
        }
    }

    return lines.join('\n');
}

function findClassArrayEnd(source, start) {
    let quote = null;
    let braceDepth = 1;
    let bracketDepth = 1;

    for (let index = start; index < source.length; index += 1) {
        const character = source[index];

        if (quote) {
            if (character === quote && source[index - 1] !== '\\') {
                quote = null;
            }

            continue;
        }

        if (character === '"' || character === "'" || character === '`') {
            quote = character;
            continue;
        }

        if (character === '{') {
            braceDepth += 1;
            continue;
        }

        if (character === '}') {
            braceDepth -= 1;

            if (braceDepth === 0 && bracketDepth === 0) {
                return index;
            }

            continue;
        }

        if (character === '[') {
            bracketDepth += 1;
            continue;
        }

        if (character === ']') {
            bracketDepth -= 1;
        }
    }

    return -1;
}

function splitTopLevelItems(source) {
    const items = [];
    let current = '';
    let quote = null;
    let braceDepth = 0;
    let bracketDepth = 0;

    for (const character of source) {
        if (quote) {
            current += character;

            if (character === quote) {
                quote = null;
            }

            continue;
        }

        if (character === '"' || character === "'" || character === '`') {
            quote = character;
            current += character;
            continue;
        }

        if (character === '{') {
            braceDepth += 1;
        }

        if (character === '}') {
            braceDepth -= 1;
        }

        if (character === '[') {
            bracketDepth += 1;
        }

        if (character === ']') {
            bracketDepth -= 1;
        }

        if (character === ',' && braceDepth === 0 && bracketDepth === 0) {
            if (current.trim()) {
                items.push(current.trim());
            }

            current = '';
            continue;
        }

        current += character;
    }

    if (current.trim()) {
        items.push(current.trim());
    }

    return items;
}

function normalizeClassCondition(condition) {
    const compact = condition.replace(/\s+/g, ' ').trim();
    const notEqualMatch = compact.match(/^(.+?)\s+!\s+(.+)$/);
    const missingEqualityMatch = compact.match(/^(.+?\.type)\s+([A-Za-z][\w-]*)$/);

    if (missingEqualityMatch && !/[=!<>]=?/.test(missingEqualityMatch[1])) {
        return `${missingEqualityMatch[1]} === "${missingEqualityMatch[2]}"`;
    }

    if (notEqualMatch) {
        const rightSide = ['light', 'akiba', 'night'].includes(notEqualMatch[2])
            ? `"${notEqualMatch[2]}"`
            : notEqualMatch[2];

        return `${notEqualMatch[1]} !== ${rightSide}`;
    }

    return compact;
}

function normalizeClassArrayItem(item) {
    const trimmed = item.replace(/,$/, '').trim();

    if (!trimmed.startsWith('{') || !trimmed.endsWith('}')) {
        return trimmed;
    }

    const content = trimmed.slice(1, -1).trim().replace(/,$/, '').trim();
    const quotedKeyMatch = content.match(/^(['"])(.*?)\1\s*:\s*([\s\S]+)$/);

    if (!quotedKeyMatch) {
        return trimmed;
    }

    const className = quotedKeyMatch[2].replace(/\s+/g, ' ');
    const condition = normalizeClassCondition(quotedKeyMatch[3]);

    return `{ "${className}": ${condition} }`;
}

function sortAttributes(attributes, tag = null) {
    const priority = (attribute) => {
        if (tag === 'label' && attribute.startsWith('for=')) {
            return 10;
        }

        if (tag === 'label' && attribute.startsWith('class=')) {
            return 20;
        }

        if (['input', 'select', 'textarea'].includes(tag) && attribute === 'required') {
            return 90;
        }

        if (attribute.startsWith('on:')) {
            return 100;
        }

        return 50;
    };

    return attributes
        .map((attribute, index) => ({ attribute, index }))
        .sort((left, right) => priority(left.attribute) - priority(right.attribute) || left.index - right.index)
        .map(({ attribute }) => attribute);
}

function normalizeClassArrays(source) {
    let formatted = '';
    let cursor = 0;

    while (cursor < source.length) {
        const start = source.indexOf('class={[', cursor);

        if (start === -1) {
            formatted += source.slice(cursor);
            break;
        }

        const end = findClassArrayEnd(source, start + 'class={['.length);

        if (end === -1) {
            formatted += source.slice(cursor);
            break;
        }

        const lineStart = source.lastIndexOf('\n', start) + 1;
        const indent = source.slice(lineStart, start).match(/^\s*/)[0];
        const itemIndent = `${indent}    `;
        const content = source.slice(start + 'class={['.length, end - 1);
        const items = splitTopLevelItems(content).map(normalizeClassArrayItem);
        const [firstItem, ...remainingItems] = items;

        formatted += source.slice(cursor, start);
        formatted += [
            `class={[${firstItem},`,
            ...remainingItems.map((item) => `${itemIndent}${item},`),
            `${indent}]}`,
        ].join('\n');
        cursor = end + 1;
    }

    return formatted;
}

function formatOpeningTag(indent, tag, rawAttributes, selfClosing) {
    let attributes = sortAttributes(getAttributes(rawAttributes), tag);

    if (attributes.length === 0) {
        return null;
    }

    if (tag === 'img') {
        attributes = attributes.filter((attribute) => !attribute.startsWith('title='));
        const altAttribute = attributes.find((attribute) => attribute.startsWith('alt='));

        if (altAttribute && !/^alt=(?:""|'')$/.test(altAttribute)) {
            attributes = attributes.filter((attribute) => !attribute.startsWith('aria-hidden='));
        } else if (!altAttribute) {
            attributes = attributes.toSpliced(1, 0, 'alt=""', 'aria-hidden="true"');
        } else if (!attributes.some((attribute) => attribute.startsWith('aria-hidden='))) {
            attributes = attributes.toSpliced(attributes.indexOf(altAttribute) + 1, 0, 'aria-hidden="true"');
        }

        return [
            `${indent}<${tag}`,
            ...attributes.map((attribute) => `${indent}    ${attribute}`),
            `${indent}${selfClosing ? '/>' : '>'}`,
        ].join('\n');
    }

    if (tag === 'Link' && attributes.length === 1) {
        return `${indent}<${tag} ${attributes[0]}${selfClosing ? ' /' : ''}>`;
    }

    if (['Link', 'a', 'input', 'select', 'textarea'].includes(tag)) {
        return [
            `${indent}<${tag}`,
            ...attributes.map((attribute) => `${indent}    ${attribute}`),
            `${indent}${selfClosing ? '/>' : '>'}`,
        ].join('\n');
    }

    if (attributes.length < 3) {
        return `${indent}<${tag} ${attributes.join(' ')}${selfClosing ? ' /' : ''}>`;
    }

    return [
        `${indent}<${tag}`,
        ...attributes.map((attribute) => `${indent}    ${attribute}`),
        `${indent}${selfClosing ? '/>' : '>'}`,
    ].join('\n');
}

function formatOpeningTags(source) {
    const lines = source.split(/\r?\n/);
    const formatted = [];

    for (let index = 0; index < lines.length; index += 1) {
        const multilineMatch = lines[index].match(/^([^\S\r\n]*)<([A-Za-z][A-Za-z0-9:-]*)$/);

        if (multilineMatch) {
            const [, indent, tag] = multilineMatch;
            const attributeLines = [];
            let cursor = index + 1;
            let closingMatch = null;

            while (cursor < lines.length) {
                closingMatch = lines[cursor].match(new RegExp(`^${indent}(\\/?)>$`));

                if (closingMatch && hasBalancedAttributeSyntax(attributeLines.join(' '))) {
                    break;
                }

                if (!lines[cursor].startsWith(`${indent}    `)) {
                    closingMatch = null;
                    break;
                }

                attributeLines.push(lines[cursor].trim());
                cursor += 1;
            }

            if (closingMatch) {
                formatted.push(formatOpeningTag(indent, tag, attributeLines.join(' '), closingMatch[1]) ?? lines[index]);
                index = cursor;
                continue;
            }
        }

        formatted.push(lines[index].replace(
            /^([^\S\r\n]*)<([A-Za-z][A-Za-z0-9:-]*)((?:\s+[^<>]*?)?)\s*(\/?)>[^\S\r\n]*$/,
            (line, indent, tag, rawAttributes, selfClosing) => {
                return formatOpeningTag(indent, tag, rawAttributes, selfClosing) ?? line;
            },
        ));
    }

    return formatInlineClassOpeningTags(formatted.join('\n'));
}

function formatInlineClassOpeningTags(source) {
    const lines = source.split(/\r?\n/);
    const formatted = [];

    for (let index = 0; index < lines.length; index += 1) {
        const match = lines[index].match(/^([^\S\r\n]*)<([A-Za-z][A-Za-z0-9:-]*)([\s\S]*class=\{\[[\s\S]*)$/);

        if (!match || lines[index].includes(']}>')) {
            formatted.push(lines[index]);
            continue;
        }

        const [, indent, tag] = match;
        const block = [lines[index]];
        let cursor = index + 1;
        let foundEnd = false;

        while (cursor < lines.length) {
            block.push(lines[cursor]);

            if (lines[cursor].trim().endsWith(']}>')) {
                foundEnd = true;
                break;
            }

            cursor += 1;
        }

        if (!foundEnd) {
            formatted.push(lines[index]);
            continue;
        }

        const opening = block.join('\n');
        const rawAttributes = opening
            .replace(new RegExp(`^${indent}<${tag}\\s*`), '')
            .replace(/>$/, '');

        formatted.push(formatOpeningTag(indent, tag, rawAttributes, ''));
        index = cursor;
    }

    return formatted.join('\n');
}

function findOpeningTagEnd(line, start) {
    let quote = null;
    let braceDepth = 0;

    for (let index = start; index < line.length; index += 1) {
        const character = line[index];

        if (quote) {
            if (character === quote && line[index - 1] !== '\\') {
                quote = null;
            }

            continue;
        }

        if (character === '"' || character === "'" || character === '`') {
            quote = character;
            continue;
        }

        if (character === '{') {
            braceDepth += 1;
            continue;
        }

        if (character === '}') {
            braceDepth -= 1;
            continue;
        }

        if (character === '>' && braceDepth === 0) {
            return index;
        }
    }

    return -1;
}

function formatInlineOpeningTags(source) {
    return source.split(/\r?\n/).map((line) => {
        const match = line.match(/^([^\S\r\n]*)<([A-Za-z][A-Za-z0-9:-]*)\s/);

        if (!match) {
            return line;
        }

        const [, indent, tag] = match;
        const tagStart = indent.length;
        const tagEnd = findOpeningTagEnd(line, tagStart);

        if (tagEnd === -1) {
            return line;
        }

        const opening = line.slice(tagStart, tagEnd + 1);
        const openingMatch = opening.match(/^<([A-Za-z][A-Za-z0-9:-]*)([\s\S]*?)(\/?)>$/);

        if (!openingMatch) {
            return line;
        }

        const formatted = formatOpeningTag(indent, tag, openingMatch[2], openingMatch[3]);

        if (!formatted || formatted.includes('\n')) {
            return line;
        }

        return `${formatted}${line.slice(tagEnd + 1)}`;
    }).join('\n');
}

function formatEmptyFormTags(source) {
    return source.replace(
        /^([^\S\r\n]*)<(input|select|textarea)\s+([^>\r\n]*)><\/\2>[^\S\r\n]*$/gm,
        (line, indent, tag, rawAttributes) => {
            return [
                formatOpeningTag(indent, tag, rawAttributes, ''),
                `${indent}</${tag}>`,
            ].join('\n');
        },
    );
}

function formatEmptyElementTags(source) {
    const excludedTags = ['img', 'input', 'select', 'textarea'];
    const lines = source.split(/\r?\n/);
    const formatted = [];

    for (let index = 0; index < lines.length; index += 1) {
        const openingMatch = lines[index].match(/^([^\S\r\n]*)<([A-Za-z][A-Za-z0-9:-]*)$/);

        if (!openingMatch) {
            formatted.push(lines[index]);
            continue;
        }

        const [, indent, tag] = openingMatch;

        if (excludedTags.includes(tag)) {
            formatted.push(lines[index]);
            continue;
        }

        const attributeLines = [];
        let cursor = index + 1;

        while (cursor < lines.length && lines[cursor].startsWith(`${indent}    `)) {
            attributeLines.push(lines[cursor].trim());
            cursor += 1;
        }

        if (attributeLines.length > 0 && lines[cursor] === `${indent}></${tag}>`) {
            const attributes = sortAttributes(getAttributes(attributeLines.join(' ')), tag);
            formatted.push(`${indent}<${tag} ${attributes.join(' ')}></${tag}>`);
            index = cursor;
            continue;
        }

        formatted.push(lines[index]);
    }

    return formatted.join('\n');
}

function hasVisibleText(content) {
    return content
        .replace(/<[^>]+>/g, '')
        .replace(/\{#[\s\S]*?\}/g, '')
        .replace(/\{\/[\s\S]*?\}/g, '')
        .replace(/\{:[\s\S]*?\}/g, '')
        .replace(/\{[\s\S]*?\}/g, '')
        .trim().length > 0;
}

function hasAttribute(attributes, name) {
    return new RegExp(`(?:^|\\s)${name}(?:\\s*=|\\s|$)`).test(attributes);
}

function formatInlineTextTernaries(source) {
    const lines = source.split(/\r?\n/);
    const formatted = [];

    for (let index = 0; index < lines.length; index += 1) {
        const conditionMatch = lines[index].match(/^([^\S\r\n]*)(.+\{.+)$/);
        const truthyMatch = lines[index + 1]?.match(/^[^\S\r\n]*\?\s+(.+)$/);
        const falsyMatch = lines[index + 2]?.match(/^[^\S\r\n]*:\s+(.+\})$/);

        if (conditionMatch && truthyMatch && falsyMatch) {
            const [, indent, start] = conditionMatch;
            formatted.push(`${indent}${start} ? ${truthyMatch[1].trim()} : ${falsyMatch[1].trim()}`);
            index += 2;
            continue;
        }

        formatted.push(lines[index]);
    }

    return formatted.join('\n');
}

function formatInlineConstTags(source) {
    return source.replace(
        /^([^\S\r\n]*)\{@const\s+([\s\S]*?)\r?\n([^\S\r\n]+)([\s\S]*?)\r?\n([^\S\r\n]+)([\s\S]*?)\}$/gm,
        (line, indent, start, secondIndent, middle, thirdIndent, end) => {
            if (secondIndent.length <= indent.length || thirdIndent.length <= indent.length) {
                return line;
            }

            return `${indent}{@const ${start.trim()} ${middle.trim()} ${end.trim()}}`;
        },
    );
}

function formatInlineRouterOptions(source) {
    const formatted = source.replace(
        /^([^\S\r\n]*)router\.(get|post|put|patch|delete)\(\r?\n([^\S\r\n]+)([^,\r\n]+),\r?\n\3([^,\r\n]+),\r?\n\3\{\r?\n([\s\S]*?)\r?\n\3\},\r?\n\1\);/gm,
        (line, indent, method, argumentIndent, url, payload, options) => {
            return [
                `${indent}router.${method}(${url.trim()}, ${payload.trim()}, {`,
                options,
                `${argumentIndent}});`,
            ].join('\n');
        },
    );

    return formatted.replace(
        /^([^\S\r\n]*)router\.(get|post|put|patch|delete)\(([^,\r\n]+),\s*([^,\r\n]+),\s*\{\r?\n([\s\S]*?)\r?\n([^\S\r\n]*)\}\);/gm,
        (line, indent, method, url, payload, options, closingIndent) => {
            const optionIndent = options.match(/^([^\S\r\n]*)\S/m)?.[1] ?? closingIndent;
            const expectedClosingIndent = optionIndent.slice(0, Math.max(indent.length, optionIndent.length - 4));

            return [
                `${indent}router.${method}(${url.trim()}, ${payload.trim()}, {`,
                options,
                `${expectedClosingIndent}});`,
            ].join('\n');
        },
    );
}

function formatInlineAxiosCalls(source) {
    return source.replace(
        /^([^\S\r\n]*)axios\r?\n\1 {4}\.(get|post|put|patch|delete)\(([^)\r\n]+)\)/gm,
        (line, indent, method, argument) => `${indent}axios.${method}(${argument})`,
    );
}

function addMissingButtonAttributes(source) {
    let formatted = '';
    let cursor = 0;

    while (cursor < source.length) {
        const start = source.indexOf('<button', cursor);

        if (start === -1) {
            formatted += source.slice(cursor);
            break;
        }

        const tagEnd = findOpeningTagEnd(source, start);

        if (tagEnd === -1) {
            formatted += source.slice(cursor);
            break;
        }

        const closeStart = source.indexOf('</button>', tagEnd + 1);

        if (closeStart === -1) {
            formatted += source.slice(cursor, tagEnd + 1);
            cursor = tagEnd + 1;
            continue;
        }

        const opening = source.slice(start, tagEnd + 1);
        const attributes = opening.match(/^<button\b([\s\S]*?)(\/?)>$/)?.[1] ?? '';
        let formattedOpening = opening;

        if (!hasAttribute(attributes, 'type')) {
            formattedOpening = formattedOpening.replace('<button', '<button type="button"');
        }

        if (!hasAttribute(attributes, 'aria-label') && !hasVisibleText(source.slice(tagEnd + 1, closeStart))) {
            formattedOpening = formattedOpening.replace('<button', '<button aria-label=""');
        }

        formatted += source.slice(cursor, start);
        formatted += formattedOpening;
        formatted += source.slice(tagEnd + 1, closeStart + '</button>'.length);
        cursor = closeStart + '</button>'.length;
    }

    return formatted;
}

function addMissingLinkAttributes(source) {
    let formatted = '';
    let cursor = 0;
    const linkPattern = /<(Link|a)\b/g;

    while (cursor < source.length) {
        linkPattern.lastIndex = cursor;
        const match = linkPattern.exec(source);

        if (!match) {
            formatted += source.slice(cursor);
            break;
        }

        const start = match.index;
        const tag = match[1];
        const tagEnd = findOpeningTagEnd(source, start);

        if (tagEnd === -1) {
            formatted += source.slice(cursor);
            break;
        }

        const closeStart = source.indexOf(`</${tag}>`, tagEnd + 1);

        if (closeStart === -1) {
            formatted += source.slice(cursor, tagEnd + 1);
            cursor = tagEnd + 1;
            continue;
        }

        const opening = source.slice(start, tagEnd + 1);
        const attributes = opening.match(new RegExp(`^<${tag}\\b([\\s\\S]*?)(\\/?)>$`))?.[1] ?? '';
        let formattedOpening = opening;
        const lineStart = source.lastIndexOf('\n', start) + 1;
        const indent = source.slice(lineStart, start);

        if (!hasVisibleText(source.slice(tagEnd + 1, closeStart))) {
            if (!hasAttribute(attributes, 'aria-label')) {
                formattedOpening = formattedOpening.replace(`<${tag}`, `<${tag} aria-label=""`);
            }

            if (!hasAttribute(attributes, 'title')) {
                formattedOpening = formattedOpening.replace(`<${tag}`, `<${tag} title=""`);
            }

            const openingMatch = formattedOpening.match(new RegExp(`^<${tag}\\b([\\s\\S]*?)(\\/?)>$`));

            if (openingMatch) {
                formattedOpening = formatOpeningTag(indent, tag, openingMatch[1], openingMatch[2]) ?? formattedOpening;
            }
        }

        formatted += source.slice(cursor, formattedOpening.startsWith(indent) ? lineStart : start);
        formatted += formattedOpening;
        formatted += source.slice(tagEnd + 1, closeStart + `</${tag}>`.length);
        cursor = closeStart + `</${tag}>`.length;
    }

    return formatted;
}

function formatSvelte(source) {
    return formatInlineAxiosCalls(formatInlineRouterOptions(formatInlineConstTags(formatInlineTextTernaries(formatEmptyElementTags(formatEmptyFormTags(formatInlineOpeningTags(formatOpeningTags(addMissingLinkAttributes(addMissingButtonAttributes(formatOpeningTags(normalizeClassArrays(repairSplitBracedAttributes(source))))))))))))).replace(
        /^([^\S\r\n]*)<([A-Za-z][A-Za-z0-9:-]*)([^>]*)>([^\S\r\n]*\S(?:[^\r\n]*\S)?[^\S\r\n]*)<\/\2>[^\S\r\n]*\r?$/gm,
        (line, indent, tag, attributes, content) => {
            const childIndent = `${indent}    `;

            return [
                `${indent}<${tag}${attributes}>`,
                `${childIndent}${content.trim()}`,
                `${indent}</${tag}>`,
            ].join('\n');
        },
    ).replace(/[^\S\r\n]+$/gm, '');
}

async function formatFile(filePath) {
    const source = await readFile(filePath, 'utf8');
    const formatted = formatSvelte(source);

    if (formatted === source) {
        return false;
    }

    await writeFile(filePath, formatted);

    return true;
}

const files = await findSvelteFiles(svelteRoot);
let changed = 0;

for (const file of files) {
    if (await formatFile(file)) {
        changed += 1;
    }
}

console.log(`Svelte formatter checked ${files.length} files and changed ${changed}.`);
