// Login form handler
if (document.getElementById('loginForm')) {
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const errorMessage = document.getElementById('errorMessage');
        errorMessage.style.display = 'none';
        
        const formData = new FormData(this);
        formData.append('action', 'login');
        
        try {
            const basePath = window.BASE_PATH || '';
            const response = await fetch(basePath + '/api/auth.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = result.redirect || basePath + '/dashboard.php';
            } else {
                errorMessage.textContent = result.message || 'Đăng nhập thất bại';
                errorMessage.style.display = 'block';
            }
        } catch (error) {
            console.error('Login error:', error);
            errorMessage.textContent = 'Có lỗi xảy ra, vui lòng thử lại';
            errorMessage.style.display = 'block';
        }
    });
}

// Register form handler
if (document.getElementById('registerForm')) {
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        errorMessage.style.display = 'none';
        successMessage.style.display = 'none';
        
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        if (password !== confirmPassword) {
            errorMessage.textContent = 'Mật khẩu xác nhận không khớp';
            errorMessage.style.display = 'block';
            return;
        }
        
        const formData = new FormData(this);
        formData.append('action', 'register');
        formData.delete('confirmPassword'); // Remove confirmPassword from form data
        
        try {
            const basePath = window.BASE_PATH || '';
            const response = await fetch(basePath + '/api/auth.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                successMessage.textContent = result.message || 'Đăng ký thành công! Đang chuyển đến trang đăng nhập...';
                successMessage.style.display = 'block';
                
                setTimeout(() => {
                    const basePath = window.BASE_PATH || '';
                    window.location.href = basePath + '/index.php';
                }, 2000);
            } else {
                errorMessage.textContent = result.message || 'Đăng ký thất bại';
                errorMessage.style.display = 'block';
            }
        } catch (error) {
            console.error('Register error:', error);
            errorMessage.textContent = 'Có lỗi xảy ra, vui lòng thử lại';
            errorMessage.style.display = 'block';
        }
    });
}

