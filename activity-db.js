window.ActivityDB = {
    today() {
        const now = new Date();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        return `${now.getFullYear()}-${month}-${day}`;
    },

    async list(category) {
        const response = await fetch(`activity_api.php?category=${encodeURIComponent(category)}`);
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Riwayat aktivitas gagal dimuat.');
        }
        return result.items;
    },

    async save(category, entry) {
        const formData = new FormData();
        formData.append('category', category);
        formData.append('option', entry.option);
        formData.append('note', entry.note || '');
        formData.append('date', entry.date || this.today());

        if (entry.image instanceof File) {
            formData.append('foto', entry.image);
        } else if (entry.image) {
            const imageBlob = await (await fetch(entry.image)).blob();
            formData.append('foto', imageBlob, 'verifikasi.jpg');
        }

        const response = await fetch('activity_api.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Aktivitas gagal disimpan.');
        }
    }
};