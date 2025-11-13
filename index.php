<?php
require_once __DIR__ . '/config/config.php';

$page_title = 'Home';
include __DIR__ . '/templates/shared/header.php';
include __DIR__ . '/templates/shared/navigation.php';
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Find Your Perfect Apprenticeship
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-blue-100">
                Connect with top employers and kickstart your career today
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <?php if (!isLoggedIn()): ?>
                    <a href="/register.php" class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-4 rounded-lg text-lg font-semibold transition shadow-lg">
                        <i class="fas fa-user-plus mr-2"></i> Get Started
                    </a>
                    <a href="/applicant/browse.php" class="bg-blue-500 text-white hover:bg-blue-400 px-8 py-4 rounded-lg text-lg font-semibold transition border-2 border-white">
                        <i class="fas fa-search mr-2"></i> Browse Opportunities
                    </a>
                <?php elseif (isApplicant()): ?>
                    <a href="/applicant/browse.php" class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-4 rounded-lg text-lg font-semibold transition shadow-lg">
                        <i class="fas fa-search mr-2"></i> Browse Apprenticeships
                    </a>
                    <a href="/applicant/dashboard.php" class="bg-blue-500 text-white hover:bg-blue-400 px-8 py-4 rounded-lg text-lg font-semibold transition border-2 border-white">
                        <i class="fas fa-tachometer-alt mr-2"></i> My Dashboard
                    </a>
                <?php else: ?>
                    <a href="/employer/create-apprenticeship.php" class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-4 rounded-lg text-lg font-semibold transition shadow-lg">
                        <i class="fas fa-plus-circle mr-2"></i> Post an Apprenticeship
                    </a>
                    <a href="/employer/dashboard.php" class="bg-blue-500 text-white hover:bg-blue-400 px-8 py-4 rounded-lg text-lg font-semibold transition border-2 border-white">
                        <i class="fas fa-tachometer-alt mr-2"></i> My Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Why Choose Our Platform?
            </h2>
            <p class="text-xl text-gray-600">
                Everything you need to find or post apprenticeship opportunities
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="text-center p-6 rounded-lg hover:shadow-xl transition">
                <div class="bg-blue-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-3xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3">Easy Search</h3>
                <p class="text-gray-600">
                    Find apprenticeships that match your skills and interests with our powerful search and filter tools.
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="text-center p-6 rounded-lg hover:shadow-xl transition">
                <div class="bg-green-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-file-upload text-3xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3">Quick Applications</h3>
                <p class="text-gray-600">
                    Upload your CV once and apply to multiple apprenticeships with just a few clicks.
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="text-center p-6 rounded-lg hover:shadow-xl transition">
                <div class="bg-purple-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chart-line text-3xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3">Track Progress</h3>
                <p class="text-gray-600">
                    Monitor your applications and receive updates on your apprenticeship journey.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                How It Works
            </h2>
            <p class="text-xl text-gray-600">
                Get started in three simple steps
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-12">
            <!-- For Applicants -->
            <div class="bg-white p-8 rounded-lg shadow-lg">
                <h3 class="text-2xl font-bold mb-6 text-blue-600">
                    <i class="fas fa-user mr-2"></i> For Applicants
                </h3>
                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="bg-blue-600 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold mr-4 flex-shrink-0">
                            1
                        </div>
                        <div>
                            <h4 class="font-semibold text-lg mb-1">Create Your Profile</h4>
                            <p class="text-gray-600">Register and upload your CV to get started</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-blue-600 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold mr-4 flex-shrink-0">
                            2
                        </div>
                        <div>
                            <h4 class="font-semibold text-lg mb-1">Browse & Apply</h4>
                            <p class="text-gray-600">Search for apprenticeships and submit applications</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-blue-600 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold mr-4 flex-shrink-0">
                            3
                        </div>
                        <div>
                            <h4 class="font-semibold text-lg mb-1">Get Hired</h4>
                            <p class="text-gray-600">Track your applications and start your career</p>
                        </div>
                    </div>
                </div>
                <?php if (!isLoggedIn() || isApplicant()): ?>
                <a href="<?php echo isLoggedIn() ? '/applicant/browse.php' : '/register.php'; ?>" class="btn btn-primary w-full mt-6">
                    <i class="fas fa-arrow-right mr-2"></i> Get Started as Applicant
                </a>
                <?php endif; ?>
            </div>

            <!-- For Employers -->
            <div class="bg-white p-8 rounded-lg shadow-lg">
                <h3 class="text-2xl font-bold mb-6 text-indigo-600">
                    <i class="fas fa-building mr-2"></i> For Employers
                </h3>
                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="bg-indigo-600 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold mr-4 flex-shrink-0">
                            1
                        </div>
                        <div>
                            <h4 class="font-semibold text-lg mb-1">Register Your Company</h4>
                            <p class="text-gray-600">Create an employer account with your company details</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-indigo-600 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold mr-4 flex-shrink-0">
                            2
                        </div>
                        <div>
                            <h4 class="font-semibold text-lg mb-1">Post Apprenticeships</h4>
                            <p class="text-gray-600">Create listings with custom application forms</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-indigo-600 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold mr-4 flex-shrink-0">
                            3
                        </div>
                        <div>
                            <h4 class="font-semibold text-lg mb-1">Find Talent</h4>
                            <p class="text-gray-600">Review applications and hire the best candidates</p>
                        </div>
                    </div>
                </div>
                <?php if (!isLoggedIn() || isEmployer()): ?>
                <a href="<?php echo isLoggedIn() ? '/employer/create-apprenticeship.php' : '/register.php'; ?>" class="btn btn-secondary w-full mt-6">
                    <i class="fas fa-arrow-right mr-2"></i> Get Started as Employer
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="py-20 bg-blue-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-5xl font-bold mb-2">500+</div>
                <div class="text-blue-200">Active Apprenticeships</div>
            </div>
            <div>
                <div class="text-5xl font-bold mb-2">1,000+</div>
                <div class="text-blue-200">Registered Applicants</div>
            </div>
            <div>
                <div class="text-5xl font-bold mb-2">200+</div>
                <div class="text-blue-200">Partner Companies</div>
            </div>
            <div>
                <div class="text-5xl font-bold mb-2">95%</div>
                <div class="text-blue-200">Success Rate</div>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
            Ready to Start Your Journey?
        </h2>
        <p class="text-xl text-gray-600 mb-8">
            Join thousands of successful apprentices and employers on our platform
        </p>
        <?php if (!isLoggedIn()): ?>
        <a href="/register.php" class="btn btn-primary text-lg px-8 py-4">
            <i class="fas fa-user-plus mr-2"></i> Create Free Account
        </a>
        <?php else: ?>
        <a href="<?php echo isApplicant() ? '/applicant/dashboard.php' : '/employer/dashboard.php'; ?>" class="btn btn-primary text-lg px-8 py-4">
            <i class="fas fa-tachometer-alt mr-2"></i> Go to Dashboard
        </a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/templates/shared/footer.php'; ?>
