(function () {
    const picker = document.getElementById('profile_picture');
    const form = document.querySelector('.settings-form');
    const editor = document.getElementById('profilePhotoEditor');
    const preview = document.getElementById('profileCropPreview');
    const context = preview.getContext('2d');
    const avatar = document.querySelector('.settings-avatar');
    const originalAvatar = [...avatar.childNodes].map(node => node.cloneNode(true));
    const originalPhotoUrl = avatar.querySelector('img')?.src;
    const thumbnail = document.createElement('canvas'); thumbnail.width = thumbnail.height = 176;
    thumbnail.setAttribute('aria-label', 'Adjusted profile photo preview');
    const zoom = document.getElementById('photoZoom');
    const horizontal = document.getElementById('photoHorizontal');
    const vertical = document.getElementById('photoVertical');
    const error = document.getElementById('profilePhotoError');
    const remove = form.querySelector('[name="remove_photo"]');
    let image = null, objectUrl = null, requestVersion = 0, submitting = false, drag = null, loading = false;
    function showError(message) { error.textContent = message; error.hidden = !message; }
    function releaseUrl() { if (objectUrl) URL.revokeObjectURL(objectUrl); objectUrl = null; }
    function clear() {
        requestVersion++; image = null; drag = null; loading = false; releaseUrl(); picker.value = ''; editor.hidden = true;
        avatar.replaceChildren(...originalAvatar.map(node => node.cloneNode(true)));
    }
    function geometry() {
        const scale = Math.max(512 / image.naturalWidth, 512 / image.naturalHeight) * Number(zoom.value);
        return { width: image.naturalWidth * scale, height: image.naturalHeight * scale };
    }
    function draw() {
        if (!image) return;
        const { width, height } = geometry();
        context.clearRect(0, 0, 512, 512);
        context.imageSmoothingEnabled = true; context.imageSmoothingQuality = 'high';
        context.drawImage(image, (512 - width) * Number(horizontal.value) / 100, (512 - height) * Number(vertical.value) / 100, width, height);
        thumbnail.getContext('2d').clearRect(0, 0, 176, 176);
        thumbnail.getContext('2d').drawImage(preview, 0, 0, 176, 176);
        document.getElementById('photoZoomValue').value = Math.round(Number(zoom.value) * 100) + '%';
        horizontal.disabled = width <= 512.1; vertical.disabled = height <= 512.1;
    }
    function reset() { zoom.value = '1'; horizontal.value = vertical.value = '50'; draw(); }
    async function load(url, version) {
        loading = true; image = null; editor.hidden = true;
        try {
            const candidate = new Image(); candidate.src = url; await candidate.decode();
            if (version !== requestVersion) return;
            if (!candidate.naturalWidth || candidate.naturalWidth > 5000 || candidate.naturalHeight > 5000) throw new Error('Choose an image no larger than 5,000 pixels on each side.');
            image = candidate; editor.hidden = false; avatar.replaceChildren(thumbnail); reset(); showError('');
            if (remove) remove.checked = false;
        } catch (e) { if (version === requestVersion) { clear(); showError(e.message || 'This image could not be loaded. Choose another photo.'); } }
        finally { if (version === requestVersion) loading = false; }
    }
    picker.addEventListener('change', function () {
        const file = picker.files[0];
        if (!file) { clear(); showError(''); return; }
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
            clear(); showError('Choose a JPG, PNG or WebP photo up to 2 MB.'); return;
        }
        requestVersion++; releaseUrl(); objectUrl = URL.createObjectURL(file); load(objectUrl, requestVersion);
    });
    document.getElementById('adjustCurrentPhoto')?.addEventListener('click', function () {
        clear(); load(originalPhotoUrl, ++requestVersion);
    });
    [zoom, horizontal, vertical].forEach(control => control.addEventListener('input', draw));
    document.getElementById('photoReset').addEventListener('click', reset);
    document.getElementById('photoCancel').addEventListener('click', () => { clear(); showError(''); });
    remove?.addEventListener('change', () => { if (remove.checked) { clear(); showError(''); } });
    preview.addEventListener('pointerdown', function (event) {
        if (!image) return;
        const { width, height } = geometry();
        drag = { id: event.pointerId, x: event.clientX, y: event.clientY, horizontal: Number(horizontal.value), vertical: Number(vertical.value), width, height };
        preview.setPointerCapture(event.pointerId); preview.classList.add('dragging');
    });
    preview.addEventListener('pointermove', function (event) {
        if (!drag || event.pointerId !== drag.id) return;
        const ratio = 512 / preview.getBoundingClientRect().width;
        if (drag.width > 512.1) horizontal.value = String(Math.max(0, Math.min(100, drag.horizontal - (event.clientX - drag.x) * ratio / (drag.width - 512) * 100)));
        if (drag.height > 512.1) vertical.value = String(Math.max(0, Math.min(100, drag.vertical - (event.clientY - drag.y) * ratio / (drag.height - 512) * 100)));
        draw();
    });
    function stopDrag() { drag = null; preview.classList.remove('dragging'); }
    preview.addEventListener('pointerup', stopDrag); preview.addEventListener('pointercancel', stopDrag); preview.addEventListener('lostpointercapture', stopDrag);
    form.addEventListener('submit', function (event) {
        if (loading) { event.preventDefault(); showError('Wait for the photo preview to load before saving.'); return; }
        if (submitting || !image) return;
        event.preventDefault();
        const submitter = event.submitter;
        const save = form.querySelector('.settings-save'); save.disabled = true;
        try {
            preview.toBlob(function (blob) {
                try {
                    if (!blob || blob.size > 2 * 1024 * 1024) throw new Error('The adjusted photo could not be saved. Try a smaller image.');
                    const transfer = new DataTransfer(); transfer.items.add(new File([blob], 'profile-cropped.png', { type: 'image/png' }));
                    picker.files = transfer.files; submitting = true; save.disabled = false; form.requestSubmit(submitter || save);
                } catch (e) { save.disabled = false; showError(e.message); }
            }, 'image/png');
        } catch (e) { save.disabled = false; showError('Unable to prepare the photo. Please select it again.'); }
    });
    window.addEventListener('pagehide', releaseUrl);
})();
