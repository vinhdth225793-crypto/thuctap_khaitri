import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // JS
                'resources/js/app.js',
                
                // CSS
                'resources/css/app.css',
                'resources/css/partials/header.css',
                'resources/css/partials/footer.css',
                'resources/css/auth/auth.css',
                
                // SCSS (giữ lại nếu cần)
                'resources/sass/app.scss',
            ],
            refresh: true,
        }),
    ],
    
    // Thêm cấu hình build để xử lý CSS
    build: {
        rollupOptions: {
            output: {
                // Đặt tên file output
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name.endsWith('.css')) {
                        return 'assets/css/[name]-[hash][extname]';
                    }
                    return 'assets/[name]-[hash][extname]';
                },
            },
        },
    },
});