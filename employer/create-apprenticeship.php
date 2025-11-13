<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth/auth.php';

requireEmployer();

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];
$errors = [];

// Get employer profile
$stmt = $pdo->prepare("SELECT * FROM employer_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$employer = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $location = sanitizeInput($_POST['location'] ?? '');
    $salary = sanitizeInput($_POST['salary'] ?? '');
    $closingDate = $_POST['closing_date'] ?? '';

    // Validation
    if (empty($title)) $errors[] = 'Title is required';
    if (empty($description)) $errors[] = 'Description is required';
    if (empty($location)) $errors[] = 'Location is required';
    if (empty($closingDate)) $errors[] = 'Closing date is required';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert apprenticeship
            $stmt = $pdo->prepare("
                INSERT INTO apprenticeships (employer_id, title, description, location, salary, closing_date)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$employer['employer_id'], $title, $description, $location, $salary, $closingDate]);
            $apprenticeshipId = $pdo->lastInsertId();

            // Insert custom fields
            if (isset($_POST['custom_fields']) && is_array($_POST['custom_fields'])) {
                $order = 0;
                foreach ($_POST['custom_fields'] as $field) {
                    if (!empty($field['label']) && !empty($field['type'])) {
                        $stmt = $pdo->prepare("
                            INSERT INTO custom_fields (apprenticeship_id, field_label, field_type, field_options, is_required, field_order)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $options = ($field['type'] === 'dropdown' && !empty($field['options'])) ? $field['options'] : null;
                        $isRequired = isset($field['required']) ? 1 : 0;
                        $stmt->execute([$apprenticeshipId, $field['label'], $field['type'], $options, $isRequired, $order++]);
                    }
                }
            }

            $pdo->commit();
            setFlashMessage('Apprenticeship listing created successfully!', 'success');
            redirect('/employer/apprenticeships.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to create listing. Please try again.';
        }
    }
}

$page_title = 'Create Apprenticeship';
include __DIR__ . '/../templates/shared/header.php';
include __DIR__ . '/../templates/shared/navigation.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Create New Apprenticeship</h1>
            <p class="text-gray-600 mt-1">Post a new apprenticeship opportunity</p>
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

        <form method="POST" class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Basic Information</h2>

                <div class="space-y-4">
                    <div class="form-group">
                        <label for="title" class="form-label">Job Title *</label>
                        <input type="text" id="title" name="title" required class="form-control"
                               placeholder="e.g. Software Development Apprentice"
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description *</label>
                        <textarea id="description" name="description" rows="8" required class="form-control"
                                  placeholder="Describe the role, responsibilities, and requirements..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="location" class="form-label">Location *</label>
                            <input type="text" id="location" name="location" required class="form-control"
                                   placeholder="e.g. London, UK"
                                   value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="salary" class="form-label">Salary/Pay</label>
                            <input type="text" id="salary" name="salary" class="form-control"
                                   placeholder="e.g. £20,000 per year"
                                   value="<?php echo htmlspecialchars($_POST['salary'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="closing_date" class="form-label">Application Closing Date *</label>
                        <input type="date" id="closing_date" name="closing_date" required class="form-control"
                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                               value="<?php echo htmlspecialchars($_POST['closing_date'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Custom Application Fields -->
            <div class="bg-white rounded-lg shadow p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Application Questions</h2>
                <p class="text-gray-600 mb-6">Add custom questions for applicants to answer</p>

                <div id="custom-fields-container" class="space-y-4 mb-4">
                    <!-- Custom fields will be added here dynamically -->
                </div>

                <button type="button" id="add-field-btn" class="btn btn-outline">
                    <i class="fas fa-plus mr-2"></i> Add Question
                </button>
            </div>

            <!-- Submit Buttons -->
            <div class="flex space-x-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check mr-2"></i> Create Listing
                </button>
                <a href="/employer/dashboard.php" class="btn btn-outline">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
let fieldCounter = 0;

function addCustomField() {
    const container = document.getElementById('custom-fields-container');
    const fieldId = fieldCounter++;

    const fieldHtml = `
        <div class="border border-gray-300 rounded-lg p-4 relative" id="field-${fieldId}">
            <button type="button" onclick="removeField(${fieldId})"
                    class="absolute top-2 right-2 text-red-500 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Question Label</label>
                    <input type="text" name="custom_fields[${fieldId}][label]"
                           class="form-control" placeholder="e.g. Why do you want this role?">
                </div>

                <div class="form-group">
                    <label class="form-label">Field Type</label>
                    <select name="custom_fields[${fieldId}][type]" class="form-control"
                            onchange="handleFieldTypeChange(${fieldId}, this.value)">
                        <option value="text">Short Text</option>
                        <option value="textarea">Long Text</option>
                        <option value="dropdown">Dropdown</option>
                        <option value="file">File Upload</option>
                    </select>
                </div>
            </div>

            <div id="dropdown-options-${fieldId}" class="hidden mt-3">
                <label class="form-label">Dropdown Options (comma separated)</label>
                <input type="text" name="custom_fields[${fieldId}][options]"
                       class="form-control" placeholder="Option 1, Option 2, Option 3">
            </div>

            <div class="mt-3">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="custom_fields[${fieldId}][required]"
                           class="rounded border-gray-300 text-blue-600">
                    <span class="ml-2 text-sm text-gray-700">Required field</span>
                </label>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', fieldHtml);
}

function removeField(fieldId) {
    const field = document.getElementById(`field-${fieldId}`);
    if (field) field.remove();
}

function handleFieldTypeChange(fieldId, type) {
    const optionsDiv = document.getElementById(`dropdown-options-${fieldId}`);
    if (optionsDiv) {
        optionsDiv.classList.toggle('hidden', type !== 'dropdown');
    }
}

document.getElementById('add-field-btn').addEventListener('click', addCustomField);

// Add one default field
addCustomField();
</script>

<?php include __DIR__ . '/../templates/shared/footer.php'; ?>
