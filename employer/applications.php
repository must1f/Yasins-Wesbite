<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth/auth.php';

requireEmployer();

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id']) && isset($_POST['status'])) {
    $applicationId = (int)$_POST['application_id'];
    $status = $_POST['status'];
    $notes = sanitizeInput($_POST['notes'] ?? '');

    $stmt = $pdo->prepare("UPDATE applications SET status = ?, notes = ? WHERE application_id = ?");
    if ($stmt->execute([$status, $notes, $applicationId])) {
        setFlashMessage('Application status updated successfully', 'success');
        redirect('/employer/applications.php');
    }
}

// Get all applications
$stmt = $pdo->prepare("
    SELECT a.*, app.title, u.name as applicant_name, u.email as applicant_email,
           apr.cv_path, apr.bio, apr.phone, apr.location
    FROM applications a
    JOIN apprenticeships app ON a.apprenticeship_id = app.apprenticeship_id
    JOIN employer_profiles ep ON app.employer_id = ep.employer_id
    JOIN applicant_profiles apr ON a.applicant_id = apr.profile_id
    JOIN users u ON apr.user_id = u.user_id
    WHERE ep.user_id = ?
    ORDER BY a.submitted_at DESC
");
$stmt->execute([$userId]);
$applications = $stmt->fetchAll();

$page_title = 'Applications';
include __DIR__ . '/../templates/shared/header.php';
include __DIR__ . '/../templates/shared/navigation.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Applications</h1>
            <p class="text-gray-600 mt-1">Review and manage candidate applications</p>
        </div>

        <?php if (empty($applications)): ?>
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No applications yet</h3>
                <p class="text-gray-600 mb-6">Applications will appear here when candidates apply to your listings</p>
                <a href="/employer/create-apprenticeship.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle mr-2"></i> Create a Listing
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($applications as $app): ?>
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition">
                        <div class="p-6">
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex-1 mb-4 lg:mb-0">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <h3 class="text-2xl font-bold text-gray-900">
                                                <?php echo htmlspecialchars($app['applicant_name']); ?>
                                            </h3>
                                            <p class="text-gray-600 mt-1">
                                                Applied for: <span class="font-semibold"><?php echo htmlspecialchars($app['title']); ?></span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mt-4">
                                        <div>
                                            <span class="text-gray-500">Email:</span>
                                            <a href="mailto:<?php echo htmlspecialchars($app['applicant_email']); ?>"
                                               class="text-blue-600 hover:text-blue-800 ml-2">
                                                <?php echo htmlspecialchars($app['applicant_email']); ?>
                                            </a>
                                        </div>
                                        <?php if ($app['phone']): ?>
                                        <div>
                                            <span class="text-gray-500">Phone:</span>
                                            <span class="ml-2"><?php echo htmlspecialchars($app['phone']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($app['location']): ?>
                                        <div>
                                            <span class="text-gray-500">Location:</span>
                                            <span class="ml-2"><?php echo htmlspecialchars($app['location']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <div>
                                            <span class="text-gray-500">Applied:</span>
                                            <span class="ml-2"><?php echo formatDate($app['submitted_at']); ?></span>
                                        </div>
                                    </div>

                                    <?php if ($app['bio']): ?>
                                    <div class="mt-4 p-3 bg-gray-50 rounded">
                                        <p class="text-sm text-gray-700"><?php echo htmlspecialchars($app['bio']); ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($app['cv_path']): ?>
                                    <div class="mt-4">
                                        <a href="/uploads/cv/<?php echo htmlspecialchars($app['cv_path']); ?>"
                                           target="_blank"
                                           class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-file-pdf mr-2"></i>
                                            Download CV
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="lg:ml-6 lg:w-64">
                                    <form method="POST" class="space-y-3">
                                        <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">

                                        <div class="form-group">
                                            <label class="form-label text-sm">Status</label>
                                            <select name="status" class="form-control text-sm">
                                                <option value="submitted" <?php echo $app['status'] === 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                                                <option value="reviewed" <?php echo $app['status'] === 'reviewed' ? 'selected' : ''; ?>>Under Review</option>
                                                <option value="shortlisted" <?php echo $app['status'] === 'shortlisted' ? 'selected' : ''; ?>>Shortlisted</option>
                                                <option value="rejected" <?php echo $app['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label text-sm">Notes</label>
                                            <textarea name="notes" rows="3" class="form-control text-sm"
                                                      placeholder="Add notes about this candidate..."><?php echo htmlspecialchars($app['notes'] ?? ''); ?></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-full text-sm">
                                            <i class="fas fa-save mr-2"></i> Update Status
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../templates/shared/footer.php'; ?>
