<?php
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../../config/config.php';
}
?>
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo and Site Name -->
            <div class="flex items-center">
                <a href="/index.php" class="flex items-center">
                    <i class="fas fa-briefcase text-blue-600 text-2xl mr-2"></i>
                    <span class="text-xl font-bold text-gray-800"><?php echo SITE_NAME; ?></span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-4">
                <?php if (isLoggedIn()): ?>
                    <?php if (isApplicant()): ?>
                        <a href="/applicant/dashboard.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                        </a>
                        <a href="/applicant/browse.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-search mr-1"></i> Browse
                        </a>
                        <a href="/applicant/applications.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-file-alt mr-1"></i> My Applications
                        </a>
                        <a href="/applicant/profile.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-user mr-1"></i> Profile
                        </a>
                    <?php elseif (isEmployer()): ?>
                        <a href="/employer/dashboard.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                        </a>
                        <a href="/employer/apprenticeships.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-briefcase mr-1"></i> My Listings
                        </a>
                        <a href="/employer/create-apprenticeship.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-plus-circle mr-1"></i> Create Listing
                        </a>
                        <a href="/employer/applications.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-inbox mr-1"></i> Applications
                        </a>
                        <a href="/employer/profile.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-building mr-1"></i> Company Profile
                        </a>
                    <?php endif; ?>

                    <div class="relative group">
                        <button class="flex items-center text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-user-circle mr-1"></i>
                            <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Account'); ?>
                            <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block">
                            <a href="/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/index.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        Home
                    </a>
                    <a href="/login.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login
                    </a>
                    <a href="/register.php" class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-user-plus mr-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center">
                <button id="mobile-menu-button" class="text-gray-700 hover:text-blue-600 focus:outline-none">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div id="mobile-menu" class="hidden md:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <?php if (isLoggedIn()): ?>
                <?php if (isApplicant()): ?>
                    <a href="/applicant/dashboard.php" class="block text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                    </a>
                    <a href="/applicant/browse.php" class="block text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-search mr-2"></i> Browse
                    </a>
                    <a href="/applicant/applications.php" class="block text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-file-alt mr-2"></i> My Applications
                    </a>
                    <a href="/applicant/profile.php" class="block text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                <?php elseif (isEmployer()): ?>
                    <a href="/employer/dashboard.php" class="block text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                    </a>
                    <a href="/employer/apprenticeships.php" class="block text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-briefcase mr-2"></i> My Listings
                    </a>
                    <a href="/employer/create-apprenticeship.php" class="block text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-plus-circle mr-2"></i> Create Listing
                    </a>
                    <a href="/employer/applications.php" class="block text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-inbox mr-2"></i> Applications
                    </a>
                    <a href="/employer/profile.php" class="block text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-building mr-2"></i> Company Profile
                    </a>
                <?php endif; ?>
                <a href="/logout.php" class="block text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            <?php else: ?>
                <a href="/index.php" class="block text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">
                    Home
                </a>
                <a href="/login.php" class="block text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </a>
                <a href="/register.php" class="block text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-user-plus mr-2"></i> Register
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
