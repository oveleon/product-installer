module.exports = {
    // Error
    "error.unknown":                                "Oops, the query could not be performed. Please try again!",
    "error.session.lost":                           "The session has expired. Please log in again!",

    // Global
    "actions.close":                                "Close",
    "actions.back":                                 "Back",
    "actions.next":                                 "Next",
    "actions.start":                                "Start",
    "actions.products":                             "Product overview",
    "actions.setup":                                "Start quick setup",
    "actions.setup.expert":                         'Start in expert mode <small><sup style="display: inline-block; color: #006494; font-weight: 600; margin-right: 9px;">ALPHA</sup></small>',

    "actions.console.toggle":                       'Open/Close Console',
    "actions.database.skip":                        'Skip and continue',
    "actions.database.migrate":                     'Datenbank migrieren',

    "type.product":                                 "Product",
    "type.package":                                 "Product package",

    "global.yes":                                   "Yes",
    "global.no":                                    "No",

    // Product
    "product.badge.registered":                     "Registered",
    "product.badge.removed":                        "Removed",
    "product.setup":                                "Set up product",
    "product.update":                               "Update product",
    "product.info":                                 "Product information",
    "product.remove":                               "Remove from the list",
    "product.no_version":                           "Sorry, the product is not available for your Contao version. Please update Contao.",

    "product.loading.remove":                       "The product will be removed",

    "product.label.title":                          "Title",
    "product.label.description":                    "Description",
    "product.label.version":                        "Current Version",
    "product.label.latestVersion":                  "Newest Version",
    "product.label.registered":                     "Product is registered",
    "product.label.registeredDate":                 "Registered at",
    "product.label.shop":                           "Purchased at",

    // Dashboard step
    "dashboard.headline":                           "Produktübersicht",
    "dashboard.noProducts":                         "You have not registered any products in this project yet.",
    "dashboard.actions.register":                   "Register product",
    "dashboard.loading":                            "Products are retrieved",

    // Upload step
    "upload.headline":                              "Upload product",
    "upload.description":                           "Please upload a product. Either drag the product file (.content) into the dashed area or click on it to open the file manager.",
    "upload.loading":                               "Product is uploading",

    // License Connector step
    "license_connector.headline":                   "Select interface",
    "license_connector.load.connector":             "Information is retrieved",
    "license_connector.load.steps":                 "Masks are loaded",
    "license_connector.load.redirect":              "Just a moment, you will be redirected soon",

    // Contao Manager
    "contao_manager.headline":                      "Contao Manager",
    "contao_manager.description":                   "In order to install the necessary dependencies, the Product Installer need access to Contao Manager. If you agree, please click on 'Authorize' and follow the further steps in Contao Manager. Afterwards, you will be led back to the installation process.",
    "contao_manager.description.notInstalled":      "In order to install the required dependencies, we need access to the Contao Manager. To continue, please install the Contao Manager and start the installation process again.",
    "contao_manager.description.success":           "The access to Contao Manager has been authorized. If you still wish to perform a manual installation, please click on 'Manual Installation'.",
    "contao_manager.authorize":                     "Authorize",
    "contao_manager.open_manager":                  "Open Contao Manager",
    "contao_manager.install.label":                 "Install dependencies manually",
    "contao_manager.install.button":                "Manual installation",
    "contao_manager.install.description":           "Your composer.json file has been prepared for manual installation. Please make sure that you have manually installed the dependencies before proceeding to the next step.",
    "contao_manager.dependencies.headline":         "Dependencies",
    "contao_manager.dependencies.installed":        "Yes, I have installed all dependencies",
    "contao_manager.connection.active":             "Connection was established",
    "contao_manager.connection.inactive":           "Authorization pending",
    "contao_manager.loading":                       "Connection to the Contao Manager is established",
    "contao_manager.loading.composer":              "The composer.json file is prepared for manual installation",
    "contao_manager.process.title":                 "System preparation",
    "contao_manager.process.description":           "The dependencies necessary for the product are checked and installed if necessary.",

    // Setup step
    "setup.headline":                               "Product setup",
    "setup.prompt.headline":                        "Product setup",
    "setup.available_imports.headline":             "Available data packages in this product",
    "setup.loading":                                "Product setup is prepared",
    "setup.loading.step":                           "Setup is carried out",
    "setup.complete":                               "Product setup successfully completed",

    // License step
    "license.headline":                             "License verification",
    "license.description":                          "Enter your license key for the product to be installed here. In the next step you can view the associated products and install or update them.",
    "license.form.label.license":                   "Product license",
    "license.form.desc.license":                    "Please enter your product license here.",
    "license.actions.next":                         "Check license",
    "license.loading":                              "Products are retrieved",

    // Product step
    "product.headline":                             "Products to be registered",

    // Install step
    "install.headline":                             "Installation",
    "install.actions.add":                          "Register more products",

    // Advertising step
    "advertising.doNotShowAgain":                   "Do not show again",

    // Manager process
    "process.contao_manager.download.title":        "Download packages",
    "process.contao_manager.download.description":  "Dependent packages are downloaded and made available.",

    "process.contao_manager.package.title":         "Integrate packages",
    "process.contao_manager.package.description":   "Packages are deposited to the project.",

    "process.contao_manager.composer.title":        "Install dependencies",
    "process.contao_manager.composer.description":  "The dependencies are installed via composer.",

    "process.contao_manager.database.title":        "Database & Migrations",
    "process.contao_manager.database.description":  "Check the database for changes.",

    // Download process
    "process.download.title":                       "Retrieve products",
    "process.download.description":                 "The products are retrieved and downloaded.",

    // Register product process
    "process.register.title":                       "Product registration",
    "process.register.description":                 "The installed products are registered.",

    // Database process
    "process.database.deletionHint":                "Deletions are not taken into account and have to be done manually via the Contao Manager.",

    // Tasks
    "task.label.setup":                             "Setup",
    "task.content_package.title":                   "Content-Package",
    "task.content_package.description":             "Content packages fill your instance with life. These can be pages, articles, modules, content elements, and so on. Use the expert mode to exclude individual contents.",
    "task.package_valid.true":                      "Ready",
    "task.package_valid.false":                     "Lack of dependencies",

    // Form
    "form.field.tables.label":                      "Tables to import",
    "form.field.tables.desc":                       "Please select here the tables you want to import.",

    "form.field.rootPage.label":                    "Root page",
    "form.field.rootPage.desc":                     "Please define where the new page structure should be embedded.",

    "form.field.files.browse":                     "Browse files",
}
