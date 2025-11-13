<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth/auth.php';

requireEmployer();

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];
$errors = [];

// Get current profile
$stmt = $pdo->prepare("
    SELECT u.*, ep.*
    FROM users u
    LEFT JOIN employer_profiles ep ON u.user_id = ep.user_id
    WHERE u.user_id = ?
");
$stmt->execute([$userId]);
$profile = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = sanitizeInput($_POST['company_name'] ?? '');
    $companyDescription = sanitizeInput($_POST['company_description'] ?? '');
    $contactNumber = sanitizeInput($_POST['contact_number'] ?? '');
    $companyWebsite = sanitizeInput($_POST['company_website'] ?? '');
    $companyAddress = sanitizeInput($_POST['company_address'] ?? '');

    if (empty($companyName)) {
        $errors[] = 'Company name is required';
    }

    if (empty($errors)) {
        try {
            // Update user name
            $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE user_id = ?");
            $stmt->execute([$companyName, $userId]);

            // Update employer profile
            $stmt = $pdo->prepare("
                UPDATE employer_profiles
                SET company_name = ?, company_description = ?, contact_number = ?,
                    company_website = ?, company_address = ?
                WHERE employer_id = ?
            ");
            $stmt->execute([
                $companyName, $companyDescription, $contactNumber,
                $companyWebsite, $companyAddress, $profile['employer_id']
            ]);

            setFlashMessage('Company profile updated successfully!', 'success');
            redirect('/employer/profile.php');
        } catch (Exception $e) {
            $errors[] = 'Failed to update profile. Please try again.';
        }
    }
}

$page_title = 'Company Profile';
include __DIR__ . '/../templates/shared/header.php';
include __DIR__ . '/../templates/shared/navigation.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Company Profile</h1>
            <p class="text-gray-600 mt-1">Update your company information</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <ul class="list-disc list-inside text-sm text-red-700">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow p-8">
            <form method="POST">
                <div class="space-y-6">
                    <!-- Company Name -->
                    <div class="form-group">
                        <label for="company_name" class="form-label">
                            <i class="fas fa-building mr-1"></i> Company Name *
                        </label>
                        <input type="text" id="company_name" name="company_name" required class="form-control"
                               value="<?php echo htmlspecialchars($profile['company_name']); ?>">
                    </div>

                    <!-- Email (readonly) -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope mr-1"></i> Email Address
                        </label>
                        <input type="email" id="email" class="form-control bg-gray-100"
                               value="<?php echo htmlspecialchars($profile['email']); ?>" readonly>
                        <small class="text-gray-500">Email cannot be changed</small>
                    </div>

                    <!-- Company Description -->
                    <div class="form-group">
                        <label for="company_description" class="form-label">
                            <i class="fas fa-align-left mr-1"></i> Company Description
                        </label>
                        <textarea id="company_description" name="company_description" rows="5" class="form-control"
                                  placeholder="Tell applicants about your company..."><?php echo htmlspecialchars($profile['company_description'] ?? ''); ?></textarea>
                    </div>

                    <!-- Contact Number -->
                    <div class="form-group">
                        <label for="contact_number" class="form-label">
                            <i class="fas fa-phone mr-1"></i> Contact Number
                        </label>
                        <input type="tel" id="contact_number" name="contact_number" class="form-control"
                               value="<?php echo htmlspecialchars($profile['contact_number'] ?? ''); ?>">
                    </div>

                    <!-- Company Website -->
                    <div class="form-group">
                        <label for="company_website" class="form-label">
                            <i class="fas fa-globe mr-1"></i> Company Website
                        </label>
                        <input type="url" id="company_website" name="company_website" class="form-control"
                               placeholder="https://www.example.com"
                               value="<?php echo htmlspecialchars($profile['company_website'] ?? ''); ?>">
                    </div>

                    <!-- Company Address -->
                    <div class="form-group">
                        <label for="company_address" class="form-label">
                            <i class="fas fa-map-marker-alt mr-1"></i> Company Address
                        </label>
                        <textarea id="company_address" name="company_address" rows="3" class="form-control"
                                  placeholder="Street address, city, postcode"><?php echo htmlspecialchars($profile['company_address'] ?? ''); ?></textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex space-x-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                        <a href="/employer/dashboard.php" class="btn btn-outline">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/shared/footer.php'; ?>
