<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nigerian CBT System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center relative overflow-hidden"
      style="background-image: url('https://images.unsplash.com/photo-1546410531-bb4caa6b424d?w=1920&q=80'); background-size: cover; background-position: center; background-attachment: fixed;">
    
    <!-- Dark Overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-green-900/85 via-blue-900/80 to-purple-900/85"></div>
    
    <!-- Animated Background Shapes -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-1/2 -left-1/4 w-96 h-96 bg-green-500/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-1/2 -right-1/4 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- Login Card Container -->
    <div class="relative z-10 w-full max-w-md px-4">
        <!-- School Header -->
        <div class="text-center mb-6">
            <div class="inline-block bg-white/10 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/20 shadow-2xl">
                <h1 class="text-4xl font-bold text-white mb-2">🎓 CAMBRIDGE</h1>
                <p class="text-white/90 text-sm font-medium italic">My school is a place of light</p>
                <p class="text-white/70 text-xs mt-1">Where dreams are shaped both day and night</p>
            </div>
        </div>

        <!-- Login Card -->
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/30">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Welcome Back</h2>
                <p class="text-gray-600">Cambridge International School Examination Portal</p>
            </div>

            <?php if($errors->any()): ?>
                <div class="bg-red-50 border-2 border-red-300 text-red-700 px-4 py-3 rounded-xl mb-6 shadow-sm">
                    <div class="flex items-center">
                        <span class="text-xl mr-2">⚠️</span>
                        <span class="font-medium"><?php echo e($errors->first()); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?php echo e(route('login.post')); ?>" method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>
                
                <div>
                    <label for="identifier" class="block text-sm font-semibold text-gray-700 mb-2">
                        📧 Email or Registration Number
                    </label>
                    <input 
                        type="text" 
                        id="identifier" 
                        name="identifier" 
                        value="<?php echo e(old('identifier')); ?>"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-500/20 transition-all outline-none"
                        placeholder="Enter email or registration number"
                        required
                        autofocus
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        🔒 Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-500/20 transition-all outline-none"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
                >
                    🚀 Login to Dashboard
                </button>
            </form>

            <!-- Demo Credentials -->
            <div class="mt-8 pt-6 border-t-2 border-gray-100">
                <p class="text-center font-semibold text-gray-700 mb-3 text-sm">🔑 Demo Credentials:</p>
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4 space-y-2 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-600">👨‍💼 Admin:</span>
                        <span class="text-gray-700">admin@school.com / password</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-600">👩‍🏫 Teacher:</span>
                        <span class="text-gray-700">okafor@school.com / password</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-600">👨‍🎓 Student:</span>
                        <span class="text-gray-700">STD2024001 / password</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-white/80 text-sm font-medium">
                © 2026 Cambridge International School
            </p>
            <p class="text-white/60 text-xs mt-1">
                Powered by Modern CBT Platform
            </p>
        </div>
    </div>
</body>
</html><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\auth\register.blade.php ENDPATH**/ ?>