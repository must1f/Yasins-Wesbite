<?php
require_once __DIR__ . '/../config/config.php';

$pdo = getDatabaseConnection();

// Get filters from GET parameters
$search = $_GET['search'] ?? '';
$location = $_GET['location'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * ITEMS_PER_PAGE;

// Build query
$where = ["app.is_active = 1", "app.closing_date >= CURDATE()"];
$params = [];

if ($search) {
    $where[] = "(app.title LIKE ? OR app.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($location) {
    $where[] = "app.location LIKE ?";
    $params[] = "%$location%";
}

$whereClause = implode(' AND ', $where);

// Get total count
$countStmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM apprenticeships app
    JOIN employer_profiles ep ON app.employer_id = ep.employer_id
    WHERE $whereClause
");
$countStmt->execute($params);
$totalCount = $countStmt->fetch()['total'];
$totalPages = ceil($totalCount / ITEMS_PER_PAGE);

// Get apprenticeships
$params[] = $offset;
$params[] = ITEMS_PER_PAGE;
$stmt = $pdo->prepare("
    SELECT app.*, ep.company_name, ep.company_description
    FROM apprenticeships app
    JOIN employer_profiles ep ON app.employer_id = ep.employer_id
    WHERE $whereClause
    ORDER BY app.created_at DESC
    LIMIT ?, ?
");
$stmt->execute($params);
$apprenticeships = $stmt->fetchAll();

$page_title = 'Browse Apprenticeships';
include __DIR__ . '/../templates/shared/header.php';
include __DIR__ . '/../templates/shared/navigation.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Browse Apprenticeships</h1>
            <p class="text-gray-600 mt-1">Find your perfect apprenticeship opportunity</p>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <form method="GET" action="/applicant/browse.php" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-group mb-0">
                    <label for="search" class="form-label">
                        <i class="fas fa-search mr-1"></i> Search Keywords
                    </label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        class="form-control"
                        placeholder="Job title, skills, company..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                </div>

                <div class="form-group mb-0">
                    <label for="location" class="form-label">
                        <i class="fas fa-map-marker-alt mr-1"></i> Location
                    </label>
                    <input
                        type="text"
                        id="location"
                        name="location"
                        class="form-control"
                        placeholder="City or postcode"
                        value="<?php echo htmlspecialchars($location); ?>"
                    >
                </div>

                <div class="flex items-end">
                    <button type="submit" class="btn btn-primary w-full">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Results Count -->
        <div class="mb-4">
            <p class="text-gray-600">
                Found <span class="font-semibold"><?php echo $totalCount; ?></span> apprenticeship<?php echo $totalCount != 1 ? 's' : ''; ?>
            </p>
        </div>

        <!-- Apprenticeships Grid -->
        <?php if (empty($apprenticeships)): ?>
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No apprenticeships found</h3>
                <p class="text-gray-600">Try adjusting your search criteria</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php foreach ($apprenticeships as $app): ?>
                    <div class="bg-white rounded-lg shadow hover:shadow-xl transition-shadow overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-3">
                                <div class="bg-blue-100 p-3 rounded-lg">
                                    <i class="fas fa-briefcase text-2xl text-blue-600"></i>
                                </div>
                                <span class="badge badge-success">Active</span>
                            </div>

                            <h3 class="text-xl font-bold text-gray-900 mb-2">
                                <?php echo htmlspecialchars($app['title']); ?>
                            </h3>

                            <p class="text-gray-600 mb-3 flex items-center">
                                <i class="fas fa-building mr-2"></i>
                                <?php echo htmlspecialchars($app['company_name']); ?>
                            </p>

                            <p class="text-gray-700 mb-4 line-clamp-3">
                                <?php echo htmlspecialchars(substr($app['description'], 0, 150)) . '...'; ?>
                            </p>

                            <div class="space-y-2 mb-4 text-sm">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-map-marker-alt mr-2 w-4"></i>
                                    <?php echo htmlspecialchars($app['location']); ?>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-pound-sign mr-2 w-4"></i>
                                    <?php echo htmlspecialchars($app['salary']); ?>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-calendar-times mr-2 w-4"></i>
                                    Closes: <?php echo formatDate($app['closing_date']); ?>
                                </div>
                            </div>

                            <a href="/applicant/apply.php?id=<?php echo $app['apprenticeship_id']; ?>" class="btn btn-primary w-full">
                                <i class="fas fa-paper-plane mr-2"></i> Apply Now
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="flex justify-center items-center space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&location=<?php echo urlencode($location); ?>" class="btn btn-outline">
                            <i class="fas fa-chevron-left mr-2"></i> Previous
                        </a>
                    <?php endif; ?>

                    <span class="text-gray-600">
                        Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                    </span>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&location=<?php echo urlencode($location); ?>" class="btn btn-outline">
                            Next <i class="fas fa-chevron-right ml-2"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../templates/shared/footer.php'; ?>
