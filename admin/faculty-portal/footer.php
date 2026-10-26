        </main>
    </div>

<script>
/* UTF-8-safe Base64 encoder */
function b64EncodeUtf8(str) {
    if (!str) return '';
    try {
        return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
            return String.fromCharCode(parseInt(p1, 16));
        }));
    } catch (e) {
        return btoa(unescape(encodeURIComponent(str)));
    }
}

/* Split into fixed-size chunks (200 chars to avoid WAF false positives) */
function splitIntoChunks(text, chunkSize) {
    const chunks = [];
    for (let i = 0; i < text.length; i += chunkSize) {
        chunks.push(text.substring(i, i + chunkSize));
    }
    return chunks;
}

/* Sync a form field into chunked hidden inputs */
function syncChunked(fieldId) {
    const visible = document.getElementById(fieldId);
    const countInput = document.getElementById(fieldId + '_chunk_count');
    const container = document.getElementById(fieldId + '_chunks_container');

    if (!visible || !countInput || !container) return;

    const base64 = b64EncodeUtf8(visible.value);
    const chunks = splitIntoChunks(base64, 200);

    countInput.value = chunks.length;
    container.innerHTML = '';

    for (let i = 0; i < chunks.length; i++) {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = fieldId + '_chunk_' + i;
        inp.value = chunks[i];
        container.appendChild(inp);
    }
}
</script>

</body>
</html>
