<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth/auth.php';

requireApplicant();

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];
$apprenticeshipId = (int)($_GET['id'] ?? 0);
$errors = [];

// Get apprenticeship details
$stmt = $pdo->prepare("
    SELECT app.*, ep.company_name, ep.company_description
    FROM apprenticeships app
    JOIN employer_profiles ep ON app.employer_id = ep.employer_id
    WHERE app.apprenticeship_id = ? AND app.is_active = 1
");
$stmt->execute([$apprenticeshipId]);
$apprenticeship = $stmt->fetch();

if (!$apprenticeship) {
    setFlashMessage('Apprenticeship not found', 'error');
    redirect('/applicant/browse.php');
}

// Check if already applied
$stmt = $pdo->prepare("
    SELECT a.application_id
    FROM applications a
    JOIN applicant_profiles ap ON a.applicant_id = ap.profile_id
    WHERE ap.user_id = ? AND a.apprenticeship_id = ?
");
$stmt->execute([$userId, $apprenticeshipId]);
if ($stmt->fetch()) {
    setFlashMessage('You have already applied to this apprenticeship', 'warning');
    redirect('/applicant/applications.php');
}

// Get applicant profile
$stmt = $pdo->prepare("SELECT * FROM applicant_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch();

// Check if CV is uploaded
if (!$profile['cv_path']) {
    setFlashMessage('Please upload your CV before applying', 'warning');
    redirect('/applicant/profile.php');
}

// Get custom fields
$stmt = $pdo->prepare("
    SELECT * FROM custom_fields
    WHERE apprenticeship_id = ?
    ORDER BY field_order, field_id
");
$stmt->execute([$apprenticeshipId]);
$customFields = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Create application
        $stmt = $pdo->prepare("
            INSERT INTO applications (apprenticeship_id, applicant_id, status)
            VALUES (?, ?, 'submitted')
        ");
        $stmt->execute([$apprenticeshipId, $profile['profile_id']]);
        $applicationId = $pdo->lastInsertId();

        // Save custom field responses
        foreach ($customFields as $field) {
            $fieldKey = 'field_' . $field['field_id'];
            $response = $_POST[$fieldKey] ?? '';

            $stmt = $pdo->prepare("
                INSERT INTO application_responses (application_id, field_id, response_text)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$applicationId, $field['field_id'], $response]);
        }

        $pdo->commit();
        setFlashMessage('Application submitted successfully!', 'success');
        redirect('/applicant/applications.php');

    } catch (Exception $e) {
        $pdo->rollBack();
        $errors[] = 'Failed to submit application. Please try again.';
    }
}

$page_title = 'Apply';
include __DIR__ . '/../templates/shared/header.php';
include __DIR__ . '/../templates/shared/navigation.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="/applicant/browse.php" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
                <i class="fas fa-arrow-left mr-2"></i> Back to Browse
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Apply for Apprenticeship</h1>
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

        <!-- Apprenticeship Details -->
        <div class="bg-white rounded-lg shadow p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                <?php echo htmlspecialchars($apprenticeship['title']); ?>
            </h2>

            <div class="flex items-center text-gray-600 mb-4">
                <i class="fas fa-building mr-2"></i>
                <span class="font-semibold"><?php echo htmlspecialchars($apprenticeship['company_name']); ?></span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
                    <?php echo htmlspecialchars($apprenticeship['location']); ?>
                </div>
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-pound-sign mr-2 text-green-600"></i>
                    <?php echo htmlspecialchars($apprenticeship['salary']); ?>
                </div>
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-calendar-times mr-2 text-red-600"></i>
                    Closes: <?php echo formatDate($apprenticeship['closing_date']); ?>
                </div>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Description:</h3>
                <p class="text-gray-700 whitespace-pre-line"><?php echo htmlspecialchars($apprenticeship['description']); ?></p>
            </div>
        </div>

        <!-- Application Form -->
        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Application Form</h2>

            <form method="POST" id="application-form">
                <!-- Profile Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Your Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Name:</span>
                            <span class="font-medium ml-2"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Email:</span>
                            <span class="font-medium ml-2"><?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">CV:</span>
                            <a href="/uploads/cv/<?php echo htmlspecialchars($profile['cv_path']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 ml-2">
                                <i class="fas fa-file-pdf mr-1"></i> View CV
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Custom Fields -->
                <?php if (!empty($customFields)): ?>
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Additional Questions</h3>
                        <div id="custom-fields-container"></div>
                    </div>
                <?php endif; ?>

                <!-- Consent -->
                <div class="form-group">
                    <div class="flex items-start">
                        <input
                            type="checkbox"
                            id="consent"
                            name="consent"
                            required
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1"
                        >
                        <label for="consent" class="ml-2 block text-sm text-gray-900">
                            I confirm that the information provided is accurate and I consent to my CV and application being shared with the employer. *
                        </label>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex space-x-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane mr-2"></i> Submit Application
                    </button>
                    <a href="/applicant/browse.php" class="btn btn-outline">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Render custom fields dynamically
const customFields = <?php echo json_encode($customFields); ?>;
const container = document.getElementById('custom-fields-container');

if (container && window.DynamicFormBuilder) {
    const formBuilder = new DynamicFormBuilder(container, customFields);
    formBuilder.render();
}
</script>

<?php include __DIR__ . '/../templates/shared/footer.php'; ?>
