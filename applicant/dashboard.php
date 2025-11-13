<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth/auth.php';

requireApplicant();

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];

// Get applicant profile
$stmt = $pdo->prepare("
    SELECT u.*, ap.*
    FROM users u
    LEFT JOIN applicant_profiles ap ON u.user_id = ap.user_id
    WHERE u.user_id = ?
");
$stmt->execute([$userId]);
$profile = $stmt->fetch();

// Get recent applications
$stmt = $pdo->prepare("
    SELECT a.*, app.title, app.location, app.salary, ep.company_name
    FROM applications a
    JOIN applicant_profiles apr ON a.applicant_id = apr.profile_id
    JOIN apprenticeships app ON a.apprenticeship_id = app.apprenticeship_id
    JOIN employer_profiles ep ON app.employer_id = ep.employer_id
    WHERE apr.user_id = ?
    ORDER BY a.submitted_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$recentApplications = $stmt->fetchAll();

// Get application statistics
$stmt = $pdo->prepare("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted,
        SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
        SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM applications a
    JOIN applicant_profiles ap ON a.applicant_id = ap.profile_id
    WHERE ap.user_id = ?
");
$stmt->execute([$userId]);
$stats = $stmt->fetch();

$page_title = 'Dashboard';
include __DIR__ . '/../templates/shared/header.php';
include __DIR__ . '/../templates/shared/navigation.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                Welcome back, <?php echo htmlspecialchars($profile['name']); ?>!
            </h1>
            <p class="text-gray-600 mt-1">Here's an overview of your apprenticeship journey</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Applications</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total']; ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-file-alt text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Shortlisted</p>
                        <p class="text-3xl font-bold text-green-600"><?php echo $stats['shortlisted']; ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-star text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Under Review</p>
                        <p class="text-3xl font-bold text-yellow-600"><?php echo $stats['reviewed']; ?></p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-clock text-2xl text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Pending</p>
                        <p class="text-3xl font-bold text-gray-600"><?php echo $stats['submitted']; ?></p>
                    </div>
                    <div class="bg-gray-100 p-3 rounded-full">
                        <i class="fas fa-hourglass-half text-2xl text-gray-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Applications -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Recent Applications</h2>
                    </div>
                    <div class="p-6">
                        <?php if (empty($recentApplications)): ?>
                            <div class="text-center py-12">
                                <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 mb-4">You haven't applied to any apprenticeships yet</p>
                                <a href="/applicant/browse.php" class="btn btn-primary">
                                    <i class="fas fa-search mr-2"></i> Browse Apprenticeships
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($recentApplications as $application): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-lg text-gray-900">
                                                    <?php echo htmlspecialchars($application['title']); ?>
                                                </h3>
                                                <p class="text-gray-600">
                                                    <i class="fas fa-building mr-1"></i>
                                                    <?php echo htmlspecialchars($application['company_name']); ?>
                                                </p>
                                            </div>
                                            <?php
                                            $statusColors = [
                                                'submitted' => 'badge-primary',
                                                'reviewed' => 'badge-warning',
                                                'shortlisted' => 'badge-success',
                                                'rejected' => 'badge-danger'
                                            ];
                                            $statusColor = $statusColors[$application['status']] ?? 'badge-primary';
                                            ?>
                                            <span class="badge <?php echo $statusColor; ?>">
                                                <?php echo ucfirst($application['status']); ?>
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap gap-4 text-sm text-gray-500 mb-2">
                                            <span>
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                <?php echo htmlspecialchars($application['location']); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-pound-sign mr-1"></i>
                                                <?php echo htmlspecialchars($application['salary']); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-calendar mr-1"></i>
                                                Applied: <?php echo formatDate($application['submitted_at']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-4 text-center">
                                <a href="/applicant/applications.php" class="text-blue-600 hover:text-blue-800 font-medium">
                                    View All Applications <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Profile Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Your Profile</h2>
                    <div class="text-center mb-4">
                        <div class="bg-blue-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-user text-4xl text-blue-600"></i>
                        </div>
                        <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($profile['name']); ?></h3>
                        <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($profile['email']); ?></p>
                    </div>

                    <div class="space-y-3 mb-4">
                        <div class="flex items-center text-sm">
                            <i class="fas fa-file-pdf text-gray-400 mr-2 w-4"></i>
                            <span class="text-gray-700">
                                CV: <?php echo $profile['cv_path'] ? 'Uploaded ✓' : 'Not uploaded'; ?>
                            </span>
                        </div>
                        <?php if ($profile['phone']): ?>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-phone text-gray-400 mr-2 w-4"></i>
                            <span class="text-gray-700"><?php echo htmlspecialchars($profile['phone']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($profile['location']): ?>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-map-marker-alt text-gray-400 mr-2 w-4"></i>
                            <span class="text-gray-700"><?php echo htmlspecialchars($profile['location']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <a href="/applicant/profile.php" class="btn btn-outline w-full">
                        <i class="fas fa-edit mr-2"></i> Edit Profile
                    </a>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="/applicant/browse.php" class="block w-full btn btn-primary text-left">
                            <i class="fas fa-search mr-2"></i> Browse Apprenticeships
                        </a>
                        <a href="/applicant/applications.php" class="block w-full btn btn-outline text-left">
                            <i class="fas fa-list mr-2"></i> View My Applications
                        </a>
                        <?php if (!$profile['cv_path']): ?>
                        <a href="/applicant/profile.php" class="block w-full btn btn-outline text-left">
                            <i class="fas fa-upload mr-2"></i> Upload CV
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/shared/footer.php'; ?>
