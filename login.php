<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $redirectUrl = isApplicant() ? '/applicant/dashboard.php' : '/employer/dashboard.php';
    redirect($redirectUrl);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    if (empty($password)) {
        $errors[] = 'Password is required';
    }

    if (empty($errors)) {
        $result = loginUser($email, $password);

        if ($result['success']) {
            setFlashMessage('Welcome back!', 'success');
            $redirectUrl = $result['user_type'] === 'applicant' ? '/applicant/dashboard.php' : '/employer/dashboard.php';
            redirect($redirectUrl);
        } else {
            $errors[] = $result['message'];
        }
    }
}

$page_title = 'Login';
include __DIR__ . '/templates/shared/header.php';
include __DIR__ . '/templates/shared/navigation.php';
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="max-w-md w-full space-y-8">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <div class="text-center">
                <i class="fas fa-sign-in-alt text-5xl text-blue-600 mb-4"></i>
                <h2 class="text-3xl font-bold text-gray-900">Sign in to your account</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Or
                    <a href="/register.php" class="font-medium text-blue-600 hover:text-blue-500">
                        create a new account
                    </a>
                </p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="mt-4 bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <ul class="list-disc list-inside text-sm text-red-700">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" method="POST" action="/login.php">
                <div class="rounded-md shadow-sm space-y-4">
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope mr-1"></i> Email Address
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="form-control"
                            placeholder="your.email@example.com"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock mr-1"></i> Password
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="form-control"
                            placeholder="Enter your password"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember-me"
                            name="remember-me"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="#" class="font-medium text-blue-600 hover:text-blue-500">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full btn btn-primary py-3 text-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                    </button>
                </div>

                <div class="text-center text-sm text-gray-600">
                    <p>Demo Accounts (password: password):</p>
                    <p class="mt-1">
                        <span class="font-semibold">Employer:</span> employer@techcorp.com
                        <br>
                        <span class="font-semibold">Applicant:</span> john@example.com
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/templates/shared/footer.php'; ?>
