/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/Resources/public/scripts/Installer/Installer.ts":
/*!*************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/Installer.ts ***!
  \*************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
eval("\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nexports.routes = void 0;\r\nconst Modal_1 = __importDefault(__webpack_require__(/*! ./components/Modal */ \"./src/Resources/public/scripts/Installer/components/Modal.ts\"));\r\nconst lang_1 = __webpack_require__(/*! ./lang/ */ \"./src/Resources/public/scripts/Installer/lang/index.js\");\r\nconst LicenserStep_1 = __importDefault(__webpack_require__(/*! ./steps/LicenserStep */ \"./src/Resources/public/scripts/Installer/steps/LicenserStep.ts\"));\r\nclass Installer {\r\n    constructor(locale) {\r\n        // Set current locale\r\n        this.setLocale(locale);\r\n        // Create modal and steps\r\n        this.modal = new Modal_1.default('installer');\r\n        this.modal.addSteps(new LicenserStep_1.default());\r\n        this.modal.appendTo('body');\r\n    }\r\n    /**\r\n     * Set current language\r\n     *\r\n     * @param locale\r\n     */\r\n    setLocale(locale) {\r\n        this.locale = locale;\r\n        (0, lang_1.setLanguage)(locale);\r\n    }\r\n    /**\r\n     * Open Installer\r\n     */\r\n    open() {\r\n        this.modal.open();\r\n    }\r\n}\r\nexports[\"default\"] = Installer;\r\nexports.routes = {\r\n    licenser: \"/contao/installer/getlicenser\",\r\n    license: \"/contao/installer/check\",\r\n    systemcheck: \"/contao/installer/install/systemcheck\"\r\n};\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/Installer.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/components/Container.ts":
/*!************************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/components/Container.ts ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
eval("\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nclass Container {\r\n    constructor(id) {\r\n        this.id = id;\r\n        this.create();\r\n    }\r\n    create() {\r\n        this.template = document.createElement('div');\r\n        this.template.id = this.id;\r\n    }\r\n    appendTo(target) {\r\n        if (target instanceof HTMLElement) {\r\n            target.append(this.template);\r\n            return;\r\n        }\r\n        document.querySelector(target).append(this.template);\r\n    }\r\n    content(html) {\r\n        this.template.innerHTML = html;\r\n    }\r\n    hide() {\r\n        this.template.hidden = true;\r\n    }\r\n    show() {\r\n        this.template.hidden = false;\r\n    }\r\n    addClass(...className) {\r\n        this.template.classList.add(...className);\r\n    }\r\n    removeClass(...className) {\r\n        this.template.classList.remove(...className);\r\n    }\r\n}\r\nexports[\"default\"] = Container;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/components/Container.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/components/Loader.ts":
/*!*********************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/components/Loader.ts ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
eval("\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nexports.LoaderMode = void 0;\r\nconst Container_1 = __importDefault(__webpack_require__(/*! ./Container */ \"./src/Resources/public/scripts/Installer/components/Container.ts\"));\r\nvar LoaderMode;\r\n(function (LoaderMode) {\r\n    LoaderMode[\"INLINE\"] = \"inlined\";\r\n    LoaderMode[\"COVER\"] = \"cover\";\r\n})(LoaderMode = exports.LoaderMode || (exports.LoaderMode = {}));\r\nclass Loader extends Container_1.default {\r\n    constructor() {\r\n        // Auto-increment id\r\n        Loader.loaderId++;\r\n        // Create container\r\n        super('loader' + Loader.loaderId);\r\n        // Add template class\r\n        this.addClass('loader');\r\n        // Create content\r\n        this.spinnerContainer = document.createElement('div');\r\n        this.spinnerContainer.classList.add('spinner');\r\n        this.spinnerContainer.innerHTML = `\r\n          <div></div>\r\n          <div></div>\r\n          <div></div>\r\n          <div></div>\r\n          <div></div>\r\n          <div></div>\r\n        `;\r\n        this.textContainer = document.createElement('p');\r\n        this.textContainer.classList.add('text');\r\n        this.template.append(this.spinnerContainer);\r\n        this.template.append(this.textContainer);\r\n        // Loader defaults\r\n        this.hide();\r\n        this.play();\r\n        this.setMode(LoaderMode.INLINE);\r\n    }\r\n    setMode(type) {\r\n        this.removeClass(LoaderMode.INLINE, LoaderMode.COVER);\r\n        this.addClass(type);\r\n    }\r\n    setText(text) {\r\n        this.textContainer.innerHTML = text;\r\n    }\r\n    hide() {\r\n        this.setText('');\r\n        super.hide();\r\n    }\r\n    play() {\r\n        this.addClass('play');\r\n    }\r\n    pause() {\r\n        this.removeClass('play');\r\n    }\r\n}\r\nexports[\"default\"] = Loader;\r\nLoader.loaderId = 0;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/components/Loader.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/components/Modal.ts":
/*!********************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/components/Modal.ts ***!
  \********************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
eval("\r\nvar __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {\r\n    if (k2 === undefined) k2 = k;\r\n    var desc = Object.getOwnPropertyDescriptor(m, k);\r\n    if (!desc || (\"get\" in desc ? !m.__esModule : desc.writable || desc.configurable)) {\r\n      desc = { enumerable: true, get: function() { return m[k]; } };\r\n    }\r\n    Object.defineProperty(o, k2, desc);\r\n}) : (function(o, m, k, k2) {\r\n    if (k2 === undefined) k2 = k;\r\n    o[k2] = m[k];\r\n}));\r\nvar __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {\r\n    Object.defineProperty(o, \"default\", { enumerable: true, value: v });\r\n}) : function(o, v) {\r\n    o[\"default\"] = v;\r\n});\r\nvar __importStar = (this && this.__importStar) || function (mod) {\r\n    if (mod && mod.__esModule) return mod;\r\n    var result = {};\r\n    if (mod != null) for (var k in mod) if (k !== \"default\" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);\r\n    __setModuleDefault(result, mod);\r\n    return result;\r\n};\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nconst Container_1 = __importDefault(__webpack_require__(/*! ./Container */ \"./src/Resources/public/scripts/Installer/components/Container.ts\"));\r\nconst Loader_1 = __importStar(__webpack_require__(/*! ./Loader */ \"./src/Resources/public/scripts/Installer/components/Loader.ts\"));\r\nclass Modal extends Container_1.default {\r\n    constructor(id) {\r\n        super(id);\r\n        this.steps = [];\r\n        // Hide modal by default\r\n        this.hide();\r\n        // Create inside container\r\n        this.insideContainer = document.createElement('div');\r\n        this.insideContainer.classList.add('inside');\r\n        this.template.append(this.insideContainer);\r\n        // Create step container\r\n        this.stepContainer = document.createElement('div');\r\n        this.stepContainer.id = 'steps';\r\n        this.insideContainer.append(this.stepContainer);\r\n        // Create loader\r\n        this.loaderElement = new Loader_1.default();\r\n        this.loaderElement.setMode(Loader_1.LoaderMode.COVER);\r\n        this.loaderElement.appendTo(this.insideContainer);\r\n    }\r\n    addSteps(...step) {\r\n        for (const s of step) {\r\n            s.addModal(this);\r\n            this.steps.push(s);\r\n            s.appendTo(this.stepContainer);\r\n        }\r\n    }\r\n    addStepsByString(step) {\r\n        // ToDo: Modify Steps: Routes can now passed to the steps (Step class should do this)\r\n        for (const s of step) {\r\n            console.log(s);\r\n        }\r\n    }\r\n    open(startIndex = 0) {\r\n        this.currentIndex = startIndex;\r\n        this.currentStep = this.steps[this.currentIndex];\r\n        // Close other\r\n        this.closeSteps();\r\n        // Show current step\r\n        this.currentStep.show();\r\n        // Show modal\r\n        this.show();\r\n    }\r\n    loader(state = true, text) {\r\n        state ?\r\n            this.loaderElement.show() :\r\n            this.loaderElement.hide();\r\n        text ?\r\n            this.loaderElement.setText(text) :\r\n            this.loaderElement.setText('');\r\n    }\r\n    next() {\r\n        this.open(++this.currentIndex);\r\n    }\r\n    prev() {\r\n        this.open(--this.currentIndex);\r\n    }\r\n    closeSteps() {\r\n        for (const step of this.steps) {\r\n            step.hide();\r\n        }\r\n    }\r\n}\r\nexports[\"default\"] = Modal;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/components/Modal.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/components/Step.ts":
/*!*******************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/components/Step.ts ***!
  \*******************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
eval("\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nconst Container_1 = __importDefault(__webpack_require__(/*! ./Container */ \"./src/Resources/public/scripts/Installer/components/Container.ts\"));\r\nclass Step extends Container_1.default {\r\n    constructor() {\r\n        // Create container\r\n        super('step' + Step.stepId++);\r\n        this.lockedForm = false;\r\n        // Steps are hidden by default\r\n        this.hide();\r\n    }\r\n    /**\r\n     * Add the modal instance\r\n     *\r\n     * @param modal\r\n     */\r\n    addModal(modal) {\r\n        this.modal = modal;\r\n    }\r\n    /**\r\n     * Overwrites the Cotnainer::show Method\r\n     */\r\n    show() {\r\n        // Update content before show\r\n        super.content(this.getTemplate());\r\n        // Bind default events\r\n        this.defaultEvents();\r\n        // Bind custom events\r\n        this.events();\r\n        // Show step\r\n        super.show();\r\n    }\r\n    /**\r\n     * Register default events\r\n     */\r\n    defaultEvents() {\r\n        var _a, _b, _c, _d;\r\n        // Default button events\r\n        (_a = this.template.querySelector('[data-close]')) === null || _a === void 0 ? void 0 : _a.addEventListener('click', () => this.modal.hide());\r\n        (_b = this.template.querySelector('[data-prev]')) === null || _b === void 0 ? void 0 : _b.addEventListener('click', () => this.modal.prev());\r\n        (_c = this.template.querySelector('[data-next]')) === null || _c === void 0 ? void 0 : _c.addEventListener('click', () => this.modal.next());\r\n        // Default form submit event\r\n        (_d = this.template.querySelector('form')) === null || _d === void 0 ? void 0 : _d.addEventListener('submit', (e) => this.formSubmit(e));\r\n    }\r\n    /**\r\n     * Handle errors\r\n     *\r\n     * @param response\r\n     */\r\n    error(response) {\r\n        // Unlock form\r\n        this.lockedForm = false;\r\n        // Check if there are field errors\r\n        if (response === null || response === void 0 ? void 0 : response.fields) {\r\n            const form = this.template.querySelector('form');\r\n            for (const f in response.fields) {\r\n                // Add error css class\r\n                form[f].parentElement.classList.add('error');\r\n                // Check if the field already has an error text\r\n                if (form[f].nextElementSibling) {\r\n                    // Change error text\r\n                    form[f].nextElementSibling.innerHTML = response.fields[f];\r\n                }\r\n                else {\r\n                    // Add error text\r\n                    const errorText = document.createElement('p');\r\n                    errorText.innerHTML = response.fields[f];\r\n                    form[f].after(errorText);\r\n                }\r\n                // Add event\r\n                form[f].addEventListener('input', () => {\r\n                    form[f].parentElement.classList.remove('error');\r\n                }, { once: true });\r\n            }\r\n        }\r\n    }\r\n    /**\r\n     * Default form submit event to validate and prevent double clicks\r\n     *\r\n     * @protected\r\n     */\r\n    formSubmit(event) {\r\n        event.preventDefault();\r\n        const form = event.target;\r\n        const data = new FormData(form);\r\n        if (!form.checkValidity()) {\r\n            form.reportValidity();\r\n            return;\r\n        }\r\n        if (!this.lockedForm) {\r\n            this.lockedForm = true;\r\n            this.submit(form, data, event);\r\n        }\r\n    }\r\n    /**\r\n     * Set events\r\n     *\r\n     * @protected\r\n     */\r\n    events() { }\r\n    /**\r\n     * Handle form submits\r\n     *\r\n     * @protected\r\n     */\r\n    submit(form, data, event) { }\r\n}\r\nexports[\"default\"] = Step;\r\nStep.stepId = 0;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/components/Step.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/steps/LicenserStep.ts":
/*!**********************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/steps/LicenserStep.ts ***!
  \**********************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
eval("\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nconst Step_1 = __importDefault(__webpack_require__(/*! ../components/Step */ \"./src/Resources/public/scripts/Installer/components/Step.ts\"));\r\nconst lang_1 = __webpack_require__(/*! ../lang/ */ \"./src/Resources/public/scripts/Installer/lang/index.js\");\r\nconst network_1 = __webpack_require__(/*! ../../Utils/network */ \"./src/Resources/public/scripts/Utils/network.js\");\r\nconst Installer_1 = __webpack_require__(/*! ../Installer */ \"./src/Resources/public/scripts/Installer/Installer.ts\");\r\nclass LicenserStep extends Step_1.default {\r\n    /**\r\n     * @inheritDoc\r\n     */\r\n    getTemplate() {\r\n        return `\r\n            <h2>${(0, lang_1.i18n)('licenser.headline')}</h2>\r\n            <form id=\"licenser-form\" autocomplete=\"off\">\r\n                <div class=\"licenser-container\"></div>\r\n            </form>\r\n        `;\r\n    }\r\n    /**\r\n     * @inheritDoc\r\n     */\r\n    events() {\r\n        // Show loader\r\n        this.modal.loader(true, (0, lang_1.i18n)('licenser.load.licenser'));\r\n        // Get licensers\r\n        (0, network_1.call)(Installer_1.routes.licenser, {}, true).then((response) => {\r\n            // Hide loader\r\n            this.modal.loader(false);\r\n            // Check errors\r\n            if (response.error) {\r\n                super.error(response);\r\n                return;\r\n            }\r\n            // Skip step if only one licenser active\r\n            if (response.licensers.length === 1) {\r\n                this.onChooseLicenser(response.licensers[0]);\r\n                return;\r\n            }\r\n            for (const licenser of response.licensers) {\r\n                this.createLicenserElement(licenser);\r\n            }\r\n        }).catch(() => {\r\n            // ToDo: Error\r\n            console.log('error catch');\r\n        });\r\n    }\r\n    /**\r\n     * On click method\r\n     *\r\n     * @param config\r\n     */\r\n    onChooseLicenser(config) {\r\n        this.modal.loader(true, (0, lang_1.i18n)('licenser.load.steps'));\r\n        this.modal.addStepsByString(config.steps);\r\n    }\r\n    /**\r\n     * Create a single licenser element\r\n     *\r\n     * @param config\r\n     */\r\n    createLicenserElement(config) {\r\n        const image = config.config.image ? `<img src=\"${config.config.image}\" alt=\"${config.config.title}\"/>` : '';\r\n        const template = document.createElement('div');\r\n        template.classList.add('licenser');\r\n        // Add click event\r\n        template.addEventListener('click', (e) => this.onChooseLicenser(config));\r\n        // Create content\r\n        template.innerHTML = `\r\n            <div class=\"image\">${image}</div>\r\n            <div class=\"content\">\r\n                <div class=\"title\">${config.config.title}</div>\r\n                <div class=\"description\">${config.config.description}</div>\r\n            </div>\r\n        `;\r\n        // Append to container\r\n        this.template.querySelector('.licenser-container').append(template);\r\n    }\r\n}\r\nexports[\"default\"] = LicenserStep;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/steps/LicenserStep.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/lang/de.js":
/*!***********************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/lang/de.js ***!
  \***********************************************************/
/***/ ((module) => {

eval("module.exports = {\r\n    // Global\r\n    \"actions.close\":                    \"Schließen\",\r\n    \"actions.back\":                     \"Zurück\",\r\n\r\n    // License step\r\n    \"licenser.headline\":                \"Bitte wählen Sie einen Produkttypen\",\r\n    \"licenser.load.licenser\":           \"Informationen werden abgerufen\",\r\n    \"licenser.load.steps\":              \"Masken werden geladen\",\r\n\r\n    // License step\r\n    \"license.headline\":                 \"Produkt registrieren\",\r\n    \"license.description\":              \"Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.\",\r\n    \"license.form.label.license\":       \"Produktlizenz\",\r\n    \"license.actions.next\":             \"Produktlizenz überprüfen\",\r\n    \"license.loading\":                  \"Produkte werden abgerufen\",\r\n\r\n    // Product step\r\n    \"product.headline\":                 \"Produkt installieren\",\r\n    \"product.actions.install\":          \"Produkt installieren\",\r\n\r\n    // Install step\r\n    \"install.headline\":                 \"Installation wird durchgeführt\",\r\n    \"install.actions.add\":              \"Weiteres Produkt registrieren\",\r\n\r\n    'install.systemcheck.title':        \"Systemüberprüfung\",\r\n    'install.systemcheck.description':  \"Vor der Installation werden alle Anforderungen sowie Abhängigkeiten im System überprüft.\",\r\n\r\n    'install.register.title':           \"Produktregistrierung\",\r\n    'install.register.description':     \"Die Domain wird für das ausgewählte Produkt registriert.\",\r\n\r\n    'install.install.title':            \"Produktinstallation\",\r\n    'install.install.description':      \"Das Produkt wird im System installiert und in zur Verfügung gestellt.\",\r\n}\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/lang/de.js?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/lang/en.js":
/*!***********************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/lang/en.js ***!
  \***********************************************************/
/***/ ((module) => {

eval("module.exports = {\r\n    // Global\r\n    \"actions.close\":                    \"Close\",\r\n    \"actions.back\":                     \"Back\",\r\n\r\n    // License step\r\n    \"licenser.headline\":                \"Please select a product type\",\r\n    \"licenser.load.licenser\":           \"Information is retrieved\",\r\n    \"licenser.load.steps\":              \"Masks are loaded\",\r\n\r\n    // License step\r\n    \"license.headline\":                 \"Register product\",\r\n    \"license.description\":              \"Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.\",\r\n    \"license.form.label.license\":       \"Product license\",\r\n    \"license.actions.next\":             \"Check license\",\r\n    \"license.loading\":                  \"Products are retrieved\",\r\n\r\n    // Product step\r\n    \"product.headline\":                 \"Install product\",\r\n    \"product.actions.install\":          \"Install product\",\r\n\r\n    // Install step\r\n    \"install.headline\":                 \"Installation\",\r\n    \"install.actions.add\":              \"Register more products\",\r\n\r\n    'install.systemcheck.title':        \"System check\",\r\n    'install.systemcheck.description':  \"Before installation, all requirements as well as dependencies in the system are checked.\",\r\n\r\n    'install.register.title':           \"Product registration\",\r\n    'install.register.description':     \"The domain is registered for the selected product.\",\r\n\r\n    'install.install.title':            \"Product installation\",\r\n    'install.install.description':      \"The product is installed in the system and put into operation.\",\r\n}\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/lang/en.js?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/lang/index.js":
/*!**************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/lang/index.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"i18n\": () => (/* binding */ i18n),\n/* harmony export */   \"setLanguage\": () => (/* binding */ setLanguage)\n/* harmony export */ });\nconst LANGUAGES = {\r\n    en: __webpack_require__(/*! ./en */ \"./src/Resources/public/scripts/Installer/lang/en.js\"),\r\n    de: __webpack_require__(/*! ./de */ \"./src/Resources/public/scripts/Installer/lang/de.js\")\r\n}\r\n\r\nlet CURRENT_LANG = navigator.language.replace(/\\-.+/i, \"\")\r\n\r\nfunction setLanguage(lang) {\r\n    CURRENT_LANG = lang;\r\n}\r\n\r\nfunction i18n(id) {\r\n    return (LANGUAGES[CURRENT_LANG] || LANGUAGES['en'])[id] || id;\r\n}\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/lang/index.js?");

/***/ }),

/***/ "./src/Resources/public/scripts/Utils/network.js":
/*!*******************************************************!*\
  !*** ./src/Resources/public/scripts/Utils/network.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"call\": () => (/* binding */ call)\n/* harmony export */ });\nasync function call(url, parameter = {}, cache = false)\r\n{\r\n    const props = {\r\n        method: 'POST',\r\n        cache: cache ? \"force-cache\" : \"no-cache\",\r\n        headers: {\r\n            'Content-Type': 'application/json',\r\n        },\r\n        body: JSON.stringify(parameter)\r\n    }\r\n\r\n    return fetch(url, props)\r\n            .then((response) => response.json())\r\n            .then((data) => data)\r\n}\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Utils/network.js?");

/***/ }),

/***/ "./src/Resources/public/scripts/index.js":
/*!***********************************************!*\
  !*** ./src/Resources/public/scripts/index.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _Installer_Installer__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Installer/Installer */ \"./src/Resources/public/scripts/Installer/Installer.ts\");\n/* harmony import */ var _Installer_Installer__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_Installer_Installer__WEBPACK_IMPORTED_MODULE_0__);\n\r\n\r\ndocument.addEventListener('DOMContentLoaded', () => {\r\n    const installer = new (_Installer_Installer__WEBPACK_IMPORTED_MODULE_0___default())('de') // ToDo: set locale\r\n\r\n    document.getElementById('product-installer')?.addEventListener('click', (e) => {\r\n        e.preventDefault()\r\n        installer.open()\r\n    })\r\n})\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/index.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./src/Resources/public/scripts/index.js");
/******/ 	
/******/ })()
;