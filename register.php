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
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $userType = $_POST['user_type'] ?? '';

    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    if (!in_array($userType, ['applicant', 'employer'])) {
        $errors[] = 'Please select an account type';
    }

    if (empty($errors)) {
        $result = registerUser($name, $email, $password, $userType);

        if ($result['success']) {
            setFlashMessage('Registration successful! Please login.', 'success');
            redirect('/login.php');
        } else {
            $errors[] = $result['message'];
        }
    }
}

$page_title = 'Register';
include __DIR__ . '/templates/shared/header.php';
include __DIR__ . '/templates/shared/navigation.php';
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="max-w-md w-full space-y-8">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <div class="text-center">
                <i class="fas fa-user-plus text-5xl text-blue-600 mb-4"></i>
                <h2 class="text-3xl font-bold text-gray-900">Create your account</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Already have an account?
                    <a href="/login.php" class="font-medium text-blue-600 hover:text-blue-500">
                        Sign in
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

            <form class="mt-8 space-y-6" method="POST" action="/register.php">
                <div class="space-y-4">
                    <!-- Account Type Selection -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-users mr-1"></i> I am a:
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <input
                                    type="radio"
                                    id="applicant"
                                    name="user_type"
                                    value="applicant"
                                    class="hidden peer"
                                    <?php echo (($_POST['user_type'] ?? '') === 'applicant') ? 'checked' : ''; ?>
                                >
                                <label
                                    for="applicant"
                                    class="block p-4 text-center border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-blue-50 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition"
                                >
                                    <i class="fas fa-user text-2xl text-blue-600"></i>
                                    <p class="mt-2 font-medium">Applicant</p>
                                    <p class="text-xs text-gray-500">Looking for apprenticeships</p>
                                </label>
                            </div>

                            <div>
                                <input
                                    type="radio"
                                    id="employer"
                                    name="user_type"
                                    value="employer"
                                    class="hidden peer"
                                    <?php echo (($_POST['user_type'] ?? '') === 'employer') ? 'checked' : ''; ?>
                                >
                                <label
                                    for="employer"
                                    class="block p-4 text-center border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-blue-50 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition"
                                >
                                    <i class="fas fa-building text-2xl text-blue-600"></i>
                                    <p class="mt-2 font-medium">Employer</p>
                                    <p class="text-xs text-gray-500">Posting apprenticeships</p>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Name Field -->
                    <div class="form-group">
                        <label for="name" class="form-label">
                            <i class="fas fa-user mr-1"></i>
                            <span id="name-label">Full Name / Company Name</span>
                        </label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            required
                            class="form-control"
                            placeholder="Enter your name"
                            value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                        >
                    </div>

                    <!-- Email Field -->
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

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock mr-1"></i> Password
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="form-control"
                            placeholder="At least 8 characters"
                        >
                        <small class="text-gray-500 text-xs">Must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock mr-1"></i> Confirm Password
                        </label>
                        <input
                            id="confirm_password"
                            name="confirm_password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="form-control"
                            placeholder="Re-enter your password"
                        >
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="flex items-start">
                        <input
                            id="terms"
                            name="terms"
                            type="checkbox"
                            required
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1"
                        >
                        <label for="terms" class="ml-2 block text-sm text-gray-900">
                            I agree to the
                            <a href="#" class="text-blue-600 hover:text-blue-500">Terms and Conditions</a>
                            and
                            <a href="#" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full btn btn-primary py-3 text-lg">
                        <i class="fas fa-user-plus mr-2"></i> Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Update name label based on selected account type
document.querySelectorAll('input[name="user_type"]').forEach(radio => {
    radio.addEventListener('change', (e) => {
        const nameLabel = document.getElementById('name-label');
        const namePlaceholder = document.getElementById('name');
        if (e.target.value === 'employer') {
            nameLabel.textContent = 'Company Name';
            namePlaceholder.placeholder = 'Enter your company name';
        } else {
            nameLabel.textContent = 'Full Name';
            namePlaceholder.placeholder = 'Enter your full name';
        }
    });
});
</script>

<?php include __DIR__ . '/templates/shared/footer.php'; ?>
