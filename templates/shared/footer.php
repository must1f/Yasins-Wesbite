    <footer class="bg-gray-800 text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- About Section -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">
                        <i class="fas fa-briefcase mr-2"></i>
                        <?php echo SITE_NAME; ?>
                    </h3>
                    <p class="text-gray-400 text-sm">
                        Connecting talented individuals with exciting apprenticeship opportunities across the UK.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="/index.php" class="text-gray-400 hover:text-white transition">
                                <i class="fas fa-home mr-2"></i> Home
                            </a>
                        </li>
                        <?php if (!isLoggedIn()): ?>
                        <li>
                            <a href="/login.php" class="text-gray-400 hover:text-white transition">
                                <i class="fas fa-sign-in-alt mr-2"></i> Login
                            </a>
                        </li>
                        <li>
                            <a href="/register.php" class="text-gray-400 hover:text-white transition">
                                <i class="fas fa-user-plus mr-2"></i> Register
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- For Applicants -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">For Applicants</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="/applicant/browse.php" class="text-gray-400 hover:text-white transition">
                                <i class="fas fa-search mr-2"></i> Browse Apprenticeships
                            </a>
                        </li>
                        <li>
                            <a href="/applicant/dashboard.php" class="text-gray-400 hover:text-white transition">
                                <i class="fas fa-tachometer-alt mr-2"></i> My Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="/applicant/applications.php" class="text-gray-400 hover:text-white transition">
                                <i class="fas fa-file-alt mr-2"></i> My Applications
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- For Employers -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">For Employers</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="/employer/create-apprenticeship.php" class="text-gray-400 hover:text-white transition">
                                <i class="fas fa-plus-circle mr-2"></i> Post an Apprenticeship
                            </a>
                        </li>
                        <li>
                            <a href="/employer/apprenticeships.php" class="text-gray-400 hover:text-white transition">
                                <i class="fas fa-briefcase mr-2"></i> Manage Listings
                            </a>
                        </li>
                        <li>
                            <a href="/employer/applications.php" class="text-gray-400 hover:text-white transition">
                                <i class="fas fa-inbox mr-2"></i> View Applications
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">
                    &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                </p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-facebook text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-twitter text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-linkedin text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-instagram text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Main JavaScript -->
    <script src="/public/js/main.js"></script>
</body>
</html>
