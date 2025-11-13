<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth/auth.php';

requireApplicant();

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];
$errors = [];
$success = false;

// Get current profile
$stmt = $pdo->prepare("
    SELECT u.*, ap.*
    FROM users u
    LEFT JOIN applicant_profiles ap ON u.user_id = ap.user_id
    WHERE u.user_id = ?
");
$stmt->execute([$userId]);
$profile = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $location = sanitizeInput($_POST['location'] ?? '');
    $bio = sanitizeInput($_POST['bio'] ?? '');

    if (empty($name)) {
        $errors[] = 'Name is required';
    }

    // Handle CV upload
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['cv'];
        $allowedTypes = ALLOWED_CV_TYPES;

        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = 'Invalid file type. Please upload PDF or Word document.';
        } elseif ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'File size must be less than 5MB';
        } else {
            $filename = 'cv_' . $userId . '_' . time() . '_' . basename($file['name']);
            $destination = CV_UPLOAD_PATH . $filename;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                $errors[] = 'Failed to upload CV';
            } else {
                $cvPath = $filename;
            }
        }
    }

    if (empty($errors)) {
        try {
            // Update user name
            $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE user_id = ?");
            $stmt->execute([$name, $userId]);

            // Update profile
            $updateFields = ["phone = ?", "location = ?", "bio = ?"];
            $updateParams = [$phone, $location, $bio];

            if (isset($cvPath)) {
                $updateFields[] = "cv_path = ?";
                $updateParams[] = $cvPath;
            }

            $updateParams[] = $profile['profile_id'];
            $stmt = $pdo->prepare("UPDATE applicant_profiles SET " . implode(', ', $updateFields) . " WHERE profile_id = ?");
            $stmt->execute($updateParams);

            setFlashMessage('Profile updated successfully!', 'success');
            redirect('/applicant/profile.php');
        } catch (Exception $e) {
            $errors[] = 'Failed to update profile. Please try again.';
        }
    }
}

$page_title = 'My Profile';
include __DIR__ . '/../templates/shared/header.php';
include __DIR__ . '/../templates/shared/navigation.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
            <p class="text-gray-600 mt-1">Update your personal information and CV</p>
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
            <form method="POST" enctype="multipart/form-data">
                <div class="space-y-6">
                    <!-- Name -->
                    <div class="form-group">
                        <label for="name" class="form-label">
                            <i class="fas fa-user mr-1"></i> Full Name *
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            required
                            class="form-control"
                            value="<?php echo htmlspecialchars($profile['name']); ?>"
                        >
                    </div>

                    <!-- Email (readonly) -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope mr-1"></i> Email Address
                        </label>
                        <input
                            type="email"
                            id="email"
                            class="form-control bg-gray-100"
                            value="<?php echo htmlspecialchars($profile['email']); ?>"
                            readonly
                        >
                        <small class="text-gray-500">Email cannot be changed</small>
                    </div>

                    <!-- Phone -->
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone mr-1"></i> Phone Number
                        </label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            class="form-control"
                            value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>"
                        >
                    </div>

                    <!-- Location -->
                    <div class="form-group">
                        <label for="location" class="form-label">
                            <i class="fas fa-map-marker-alt mr-1"></i> Location
                        </label>
                        <input
                            type="text"
                            id="location"
                            name="location"
                            class="form-control"
                            placeholder="City, postcode"
                            value="<?php echo htmlspecialchars($profile['location'] ?? ''); ?>"
                        >
                    </div>

                    <!-- Bio -->
                    <div class="form-group">
                        <label for="bio" class="form-label">
                            <i class="fas fa-align-left mr-1"></i> About Me
                        </label>
                        <textarea
                            id="bio"
                            name="bio"
                            rows="5"
                            class="form-control"
                            placeholder="Tell employers about yourself..."
                        ><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                    </div>

                    <!-- CV Upload -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-file-pdf mr-1"></i> CV/Resume
                        </label>

                        <?php if ($profile['cv_path']): ?>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <i class="fas fa-file-pdf text-blue-600 mr-2"></i>
                                        <span class="text-sm text-gray-700">Current CV: <?php echo htmlspecialchars($profile['cv_path']); ?></span>
                                    </div>
                                    <a href="/uploads/cv/<?php echo htmlspecialchars($profile['cv_path']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-download mr-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="file-upload-wrapper">
                            <input
                                type="file"
                                id="cv"
                                name="cv"
                                accept=".pdf,.doc,.docx"
                            >
                            <label for="cv" class="file-upload-label w-full">
                                <i class="fas fa-upload mr-2"></i>
                                <?php echo $profile['cv_path'] ? 'Upload New CV' : 'Upload CV'; ?>
                            </label>
                        </div>
                        <small class="text-gray-500">Accepted formats: PDF, DOC, DOCX (Max 5MB)</small>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex space-x-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                        <a href="/applicant/dashboard.php" class="btn btn-outline">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/shared/footer.php'; ?>
