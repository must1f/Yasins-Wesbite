<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth/auth.php';

requireEmployer();

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];

// Get employer profile
$stmt = $pdo->prepare("SELECT * FROM employer_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$employer = $stmt->fetch();

// Handle delete
if (isset($_GET['delete']) && $_GET['delete']) {
    $apprenticeshipId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM apprenticeships WHERE apprenticeship_id = ? AND employer_id = ?");
    if ($stmt->execute([$apprenticeshipId, $employer['employer_id']])) {
        setFlashMessage('Apprenticeship deleted successfully', 'success');
        redirect('/employer/apprenticeships.php');
    }
}

// Get all apprenticeships
$stmt = $pdo->prepare("
    SELECT app.*, COUNT(a.application_id) as application_count
    FROM apprenticeships app
    LEFT JOIN applications a ON app.apprenticeship_id = a.apprenticeship_id
    WHERE app.employer_id = ?
    GROUP BY app.apprenticeship_id
    ORDER BY app.created_at DESC
");
$stmt->execute([$employer['employer_id']]);
$apprenticeships = $stmt->fetchAll();

$page_title = 'My Listings';
include __DIR__ . '/../templates/shared/header.php';
include __DIR__ . '/../templates/shared/navigation.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Apprenticeship Listings</h1>
                <p class="text-gray-600 mt-1">Manage your posted opportunities</p>
            </div>
            <a href="/employer/create-apprenticeship.php" class="btn btn-primary">
                <i class="fas fa-plus-circle mr-2"></i> Create New Listing
            </a>
        </div>

        <?php if (empty($apprenticeships)): ?>
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <i class="fas fa-briefcase text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No listings yet</h3>
                <p class="text-gray-600 mb-6">Create your first apprenticeship listing to start receiving applications</p>
                <a href="/employer/create-apprenticeship.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle mr-2"></i> Create Listing
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-6">
                <?php foreach ($apprenticeships as $app): ?>
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900">
                                            <?php echo htmlspecialchars($app['title']); ?>
                                        </h3>
                                        <?php if ($app['is_active']): ?>
                                            <span class="badge badge-success mt-2">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger mt-2">Inactive</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <p class="text-gray-700 mb-4"><?php echo nl2br(htmlspecialchars(substr($app['description'], 0, 200))); ?>...</p>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Location:</span>
                                        <span class="font-medium ml-1"><?php echo htmlspecialchars($app['location']); ?></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Salary:</span>
                                        <span class="font-medium ml-1"><?php echo htmlspecialchars($app['salary'] ?: 'Not specified'); ?></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Closing Date:</span>
                                        <span class="font-medium ml-1"><?php echo formatDate($app['closing_date']); ?></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Applications:</span>
                                        <span class="font-medium ml-1"><?php echo $app['application_count']; ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="ml-6 flex flex-col space-y-2">
                                <a href="/employer/applications.php?listing=<?php echo $app['apprenticeship_id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-inbox mr-1"></i> View Applications
                                </a>
                                <button onclick="if(confirm('Are you sure you want to delete this listing?')) location.href='/employer/apprenticeships.php?delete=<?php echo $app['apprenticeship_id']; ?>'"
                                        class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../templates/shared/footer.php'; ?>
