export default () => ({
    step: 1,
    errorMessage: '',
    selectedLevels: ['SMP'],
    fileNames: { cv: null, ktp: null, transcript: null, certificate: null },
    levels: [
        { id: 'SD', desc: 'Tematik, Semua Mata Pelajaran' },
        { id: 'SMP', desc: 'Mata Pelajaran Terpisah' },
        { id: 'SMA', desc: 'Mata Pelajaran Spesifik' },
        { id: 'UMUM', desc: 'Upgrade Skill/Kemampuan' }
    ],

    toggleLevel(id) {
        if (this.selectedLevels.includes(id)) {
            this.selectedLevels = this.selectedLevels.filter(item => item !== id);
        } else {
            this.selectedLevels.push(id);
        }
    },

    handleFileUpload(event, type) {
        const file = event.target.files[0];
        if (!file) return;

        const fileName = file.name.toLowerCase();
        const isPdf = file.type === 'application/pdf' || fileName.endsWith('.pdf');
        const isImage = file.type.startsWith('image/') || fileName.endsWith('.jpg') || fileName.endsWith('.png') || fileName.endsWith('.jpeg');

        // 1. Validasi Berkas CV (Wajib PDF)
        if (type === 'cv' && !isPdf) {
            this.errorMessage = 'File CV wajib diunggah dalam format PDF!';
            this.resetFile(event, type);
            return;
        }

        // 2. Validasi Transkrip / KHS (Wajib PDF)
        if (type === 'transcript' && !isPdf) {
            this.errorMessage = 'Scan Transkrip/KHS wajib diunggah dalam format PDF!';
            this.resetFile(event, type);
            return;
        }

        // 3. Validasi KTP / KTM (Bisa PDF atau Image)
        if (type === 'ktp' && !isPdf && !isImage) {
            this.errorMessage = 'Foto KTP/KTM harus berformat PDF, JPG, atau PNG!';
            this.resetFile(event, type);
            return;
        }

        // 4. Validasi Ukuran File (Maksimal 10 MB)
        if (file.size > 10 * 1024 * 1024) {
            this.errorMessage = 'Ukuran file melebihi batas maksimal 10 MB!';
            this.resetFile(event, type);
            return;
        }

        // Jika Lolos Validasi Client-Side
        this.errorMessage = '';
        this.fileNames[type] = file.name;
    },

    resetFile(event, type) {
        this.fileNames[type] = null;
        if (event.target) {
            event.target.value = '';
        }
    }
});