<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth/auth.php';

requireEmployer();

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];

// Get employer profile
$stmt = $pdo->prepare("
    SELECT u.*, ep.*
    FROM users u
    LEFT JOIN employer_profiles ep ON u.user_id = ep.user_id
    WHERE u.user_id = ?
");
$stmt->execute([$userId]);
$profile = $stmt->fetch();

// Get statistics
$stmt = $pdo->prepare("
    SELECT
        COUNT(DISTINCT app.apprenticeship_id) as total_listings,
        COUNT(DISTINCT CASE WHEN app.is_active = 1 THEN app.apprenticeship_id END) as active_listings,
        COUNT(DISTINCT a.application_id) as total_applications,
        COUNT(DISTINCT CASE WHEN a.status = 'submitted' THEN a.application_id END) as new_applications,
        COUNT(DISTINCT CASE WHEN a.status = 'shortlisted' THEN a.application_id END) as shortlisted
    FROM employer_profiles ep
    LEFT JOIN apprenticeships app ON ep.employer_id = app.employer_id
    LEFT JOIN applications a ON app.apprenticeship_id = a.apprenticeship_id
    WHERE ep.user_id = ?
");
$stmt->execute([$userId]);
$stats = $stmt->fetch();

// Get recent applications
$stmt = $pdo->prepare("
    SELECT a.*, app.title, u.name as applicant_name, apr.cv_path
    FROM applications a
    JOIN apprenticeships app ON a.apprenticeship_id = app.apprenticeship_id
    JOIN employer_profiles ep ON app.employer_id = ep.employer_id
    JOIN applicant_profiles apr ON a.applicant_id = apr.profile_id
    JOIN users u ON apr.user_id = u.user_id
    WHERE ep.user_id = ?
    ORDER BY a.submitted_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$recentApplications = $stmt->fetchAll();

// Get active apprenticeships
$stmt = $pdo->prepare("
    SELECT app.*, COUNT(a.application_id) as application_count
    FROM apprenticeships app
    JOIN employer_profiles ep ON app.employer_id = ep.employer_id
    LEFT JOIN applications a ON app.apprenticeship_id = a.apprenticeship_id
    WHERE ep.user_id = ? AND app.is_active = 1
    GROUP BY app.apprenticeship_id
    ORDER BY app.created_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$activeListings = $stmt->fetchAll();

$page_title = 'Dashboard';
include __DIR__ . '/../templates/shared/header.php';
include __DIR__ . '/../templates/shared/navigation.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                Welcome, <?php echo htmlspecialchars($profile['company_name']); ?>!
            </h1>
            <p class="text-gray-600 mt-1">Manage your apprenticeship listings and applications</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Active Listings</p>
                        <p class="text-3xl font-bold text-blue-600"><?php echo $stats['active_listings']; ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-briefcase text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Applications</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total_applications']; ?></p>
                    </div>
                    <div class="bg-gray-100 p-3 rounded-full">
                        <i class="fas fa-inbox text-2xl text-gray-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">New Applications</p>
                        <p class="text-3xl font-bold text-orange-600"><?php echo $stats['new_applications']; ?></p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-bell text-2xl text-orange-600"></i>
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
                        <p class="text-gray-500 text-sm">Total Listings</p>
                        <p class="text-3xl font-bold text-purple-600"><?php echo $stats['total_listings']; ?></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-list text-2xl text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Applications -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-900">Recent Applications</h2>
                    <a href="/employer/applications.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="p-6">
                    <?php if (empty($recentApplications)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No applications yet</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($recentApplications as $app): ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($app['applicant_name']); ?></h4>
                                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($app['title']); ?></p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                <i class="fas fa-calendar mr-1"></i>
                                                <?php echo formatDate($app['submitted_at']); ?>
                                            </p>
                                        </div>
                                        <span class="badge badge-primary"><?php echo ucfirst($app['status']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Active Listings -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-900">Active Listings</h2>
                    <a href="/employer/apprenticeships.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="p-6">
                    <?php if (empty($activeListings)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-briefcase text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500 mb-4">No active listings</p>
                            <a href="/employer/create-apprenticeship.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-2"></i> Create Listing
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($activeListings as $listing): ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($listing['title']); ?></h4>
                                            <p class="text-sm text-gray-600 mt-1">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                <?php echo htmlspecialchars($listing['location']); ?>
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                <i class="fas fa-inbox mr-1"></i>
                                                <?php echo $listing['application_count']; ?> application<?php echo $listing['application_count'] != 1 ? 's' : ''; ?>
                                            </p>
                                        </div>
                                        <span class="badge badge-success">Active</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="/employer/create-apprenticeship.php" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg shadow-lg p-6 hover:from-blue-600 hover:to-blue-700 transition">
                <i class="fas fa-plus-circle text-3xl mb-3"></i>
                <h3 class="text-lg font-semibold">Create New Listing</h3>
                <p class="text-sm text-blue-100 mt-1">Post a new apprenticeship opportunity</p>
            </a>

            <a href="/employer/applications.php" class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg shadow-lg p-6 hover:from-green-600 hover:to-green-700 transition">
                <i class="fas fa-inbox text-3xl mb-3"></i>
                <h3 class="text-lg font-semibold">View Applications</h3>
                <p class="text-sm text-green-100 mt-1">Review and manage candidate applications</p>
            </a>

            <a href="/employer/profile.php" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg shadow-lg p-6 hover:from-purple-600 hover:to-purple-700 transition">
                <i class="fas fa-building text-3xl mb-3"></i>
                <h3 class="text-lg font-semibold">Company Profile</h3>
                <p class="text-sm text-purple-100 mt-1">Update your company information</p>
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/shared/footer.php'; ?>
