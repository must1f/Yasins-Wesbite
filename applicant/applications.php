<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth/auth.php';

requireApplicant();

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];

// Get all applications
$stmt = $pdo->prepare("
    SELECT a.*, app.title, app.location, app.salary, app.closing_date, ep.company_name
    FROM applications a
    JOIN applicant_profiles apr ON a.applicant_id = apr.profile_id
    JOIN apprenticeships app ON a.apprenticeship_id = app.apprenticeship_id
    JOIN employer_profiles ep ON app.employer_id = ep.employer_id
    WHERE apr.user_id = ?
    ORDER BY a.submitted_at DESC
");
$stmt->execute([$userId]);
$applications = $stmt->fetchAll();

$page_title = 'My Applications';
include __DIR__ . '/../templates/shared/header.php';
include __DIR__ . '/../templates/shared/navigation.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Applications</h1>
            <p class="text-gray-600 mt-1">Track all your apprenticeship applications</p>
        </div>

        <?php if (empty($applications)): ?>
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No applications yet</h3>
                <p class="text-gray-600 mb-6">Start applying to apprenticeships to see them here</p>
                <a href="/applicant/browse.php" class="btn btn-primary">
                    <i class="fas fa-search mr-2"></i> Browse Apprenticeships
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($applications as $app): ?>
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="flex-1 mb-4 md:mb-0">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">
                                            <?php echo htmlspecialchars($app['title']); ?>
                                        </h3>
                                        <p class="text-gray-600 flex items-center mt-1">
                                            <i class="fas fa-building mr-2"></i>
                                            <?php echo htmlspecialchars($app['company_name']); ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-4 text-sm text-gray-500 mt-3">
                                    <span>
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <?php echo htmlspecialchars($app['location']); ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-pound-sign mr-1"></i>
                                        <?php echo htmlspecialchars($app['salary']); ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-calendar mr-1"></i>
                                        Applied: <?php echo formatDate($app['submitted_at']); ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-calendar-times mr-1"></i>
                                        Closes: <?php echo formatDate($app['closing_date']); ?>
                                    </span>
                                </div>

                                <?php if ($app['notes']): ?>
                                    <div class="mt-3 bg-yellow-50 border-l-4 border-yellow-400 p-3">
                                        <p class="text-sm text-gray-700">
                                            <strong>Employer Note:</strong> <?php echo htmlspecialchars($app['notes']); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex flex-col items-end space-y-2">
                                <?php
                                $statusConfig = [
                                    'submitted' => ['badge' => 'badge-primary', 'icon' => 'fa-paper-plane', 'text' => 'Submitted'],
                                    'reviewed' => ['badge' => 'badge-warning', 'icon' => 'fa-eye', 'text' => 'Under Review'],
                                    'shortlisted' => ['badge' => 'badge-success', 'icon' => 'fa-star', 'text' => 'Shortlisted'],
                                    'rejected' => ['badge' => 'badge-danger', 'icon' => 'fa-times-circle', 'text' => 'Not Selected']
                                ];
                                $status = $statusConfig[$app['status']] ?? $statusConfig['submitted'];
                                ?>
                                <span class="badge <?php echo $status['badge']; ?> text-sm px-4 py-2">
                                    <i class="fas <?php echo $status['icon']; ?> mr-1"></i>
                                    <?php echo $status['text']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../templates/shared/footer.php'; ?>
