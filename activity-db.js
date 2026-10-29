window.ActivityDB = {
    today() {
        const now = new Date();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        return `${now.getFullYear()}-${month}-${day}`;
    },

    async list(category, studentId = null) {
        const params = new URLSearchParams({ category });
        const pageParams = new URLSearchParams(window.location.search);
        const requestedStudentId = studentId ?? pageParams.get('id');

        if (requestedStudentId !== null && requestedStudentId !== '') {
            const parsedStudentId = Number(requestedStudentId);
            if (!Number.isSafeInteger(parsedStudentId) || parsedStudentId < 1) {
                throw new Error('ID siswa tidak valid.');
            }
            params.set('id', String(parsedStudentId));
        }

        const requestedClassId = pageParams.get('kelas_id');
        if (requestedClassId !== null) {
            const parsedClassId = Number(requestedClassId);
            if (!Number.isSafeInteger(parsedClassId) || parsedClassId < 1) {
                throw new Error('ID kelas tidak valid.');
            }
            params.set('kelas_id', String(parsedClassId));
        }

        const response = await fetch(`activity_api.php?${params.toString()}`);
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

        if (typeof File !== 'undefined' && entry.image instanceof File) {
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