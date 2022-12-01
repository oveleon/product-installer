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
eval("\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nexports.routes = void 0;\r\nconst Modal_1 = __importDefault(__webpack_require__(/*! ./components/Modal */ \"./src/Resources/public/scripts/Installer/components/Modal.ts\"));\r\nconst lang_1 = __webpack_require__(/*! ./lang/ */ \"./src/Resources/public/scripts/Installer/lang/index.js\");\r\nconst steps_1 = __webpack_require__(/*! ./steps */ \"./src/Resources/public/scripts/Installer/steps/index.ts\");\r\nclass Installer {\r\n    constructor(locale) {\r\n        // Set current locale\r\n        this.setLocale(locale);\r\n        // Create modal and steps\r\n        this.modal = new Modal_1.default('installer');\r\n        this.modal.addSteps(new steps_1.LicenseStep(), new steps_1.ProductStep(), new steps_1.InstallStep());\r\n        this.modal.appendTo('body');\r\n    }\r\n    /**\r\n     * Set current language\r\n     *\r\n     * @param locale\r\n     */\r\n    setLocale(locale) {\r\n        this.locale = locale;\r\n        (0, lang_1.setLanguage)(locale);\r\n    }\r\n    /**\r\n     * Open Installer\r\n     */\r\n    open() {\r\n        this.modal.open();\r\n    }\r\n}\r\nexports[\"default\"] = Installer;\r\nexports.routes = {\r\n    license: \"/contao/installer/check\",\r\n    systemcheck: \"/contao/installer/install/systemcheck\"\r\n};\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/Installer.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/State.ts":
/*!*********************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/State.ts ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
eval("\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nclass State {\r\n    static set(name, value) {\r\n        State.state[name] = value;\r\n    }\r\n    static get(name) {\r\n        return State.state[name];\r\n    }\r\n    static clear(name) {\r\n        if (name) {\r\n            delete State.state[name];\r\n            return;\r\n        }\r\n        State.state = {};\r\n    }\r\n}\r\nexports[\"default\"] = State;\r\nState.state = {};\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/State.ts?");

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
eval("\r\nvar __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {\r\n    if (k2 === undefined) k2 = k;\r\n    var desc = Object.getOwnPropertyDescriptor(m, k);\r\n    if (!desc || (\"get\" in desc ? !m.__esModule : desc.writable || desc.configurable)) {\r\n      desc = { enumerable: true, get: function() { return m[k]; } };\r\n    }\r\n    Object.defineProperty(o, k2, desc);\r\n}) : (function(o, m, k, k2) {\r\n    if (k2 === undefined) k2 = k;\r\n    o[k2] = m[k];\r\n}));\r\nvar __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {\r\n    Object.defineProperty(o, \"default\", { enumerable: true, value: v });\r\n}) : function(o, v) {\r\n    o[\"default\"] = v;\r\n});\r\nvar __importStar = (this && this.__importStar) || function (mod) {\r\n    if (mod && mod.__esModule) return mod;\r\n    var result = {};\r\n    if (mod != null) for (var k in mod) if (k !== \"default\" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);\r\n    __setModuleDefault(result, mod);\r\n    return result;\r\n};\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nconst Container_1 = __importDefault(__webpack_require__(/*! ./Container */ \"./src/Resources/public/scripts/Installer/components/Container.ts\"));\r\nconst Loader_1 = __importStar(__webpack_require__(/*! ./Loader */ \"./src/Resources/public/scripts/Installer/components/Loader.ts\"));\r\nclass Modal extends Container_1.default {\r\n    constructor(id) {\r\n        super(id);\r\n        this.steps = [];\r\n        // Hide modal by default\r\n        this.hide();\r\n        // Create inside container\r\n        this.insideContainer = document.createElement('div');\r\n        this.insideContainer.classList.add('inside');\r\n        this.template.append(this.insideContainer);\r\n        // Create step container\r\n        this.stepContainer = document.createElement('div');\r\n        this.stepContainer.id = 'steps';\r\n        this.insideContainer.append(this.stepContainer);\r\n        // Create loader\r\n        this.loaderElement = new Loader_1.default();\r\n        this.loaderElement.setMode(Loader_1.LoaderMode.COVER);\r\n        this.loaderElement.appendTo(this.insideContainer);\r\n    }\r\n    addSteps(...step) {\r\n        for (const s of step) {\r\n            s.addModal(this);\r\n            this.steps.push(s);\r\n            s.appendTo(this.stepContainer);\r\n        }\r\n    }\r\n    open(startIndex = 0) {\r\n        this.currentIndex = startIndex;\r\n        this.currentStep = this.steps[this.currentIndex];\r\n        // Close other\r\n        this.closeSteps();\r\n        // Show current step\r\n        this.currentStep.show();\r\n        // Show modal\r\n        this.show();\r\n    }\r\n    loader(state = true, text) {\r\n        state ?\r\n            this.loaderElement.show() :\r\n            this.loaderElement.hide();\r\n        text ?\r\n            this.loaderElement.setText(text) :\r\n            this.loaderElement.setText('');\r\n    }\r\n    next() {\r\n        this.open(++this.currentIndex);\r\n    }\r\n    prev() {\r\n        this.open(--this.currentIndex);\r\n    }\r\n    closeSteps() {\r\n        for (const step of this.steps) {\r\n            step.hide();\r\n        }\r\n    }\r\n}\r\nexports[\"default\"] = Modal;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/components/Modal.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/components/Step.ts":
/*!*******************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/components/Step.ts ***!
  \*******************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
eval("\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nconst Container_1 = __importDefault(__webpack_require__(/*! ./Container */ \"./src/Resources/public/scripts/Installer/components/Container.ts\"));\r\nclass Step extends Container_1.default {\r\n    constructor() {\r\n        // Create container\r\n        super('step' + Step.stepId++);\r\n        this.lockedForm = false;\r\n        // Steps are hidden by default\r\n        this.hide();\r\n    }\r\n    /**\r\n     * Add the modal instance\r\n     *\r\n     * @param modal\r\n     */\r\n    addModal(modal) {\r\n        this.modal = modal;\r\n    }\r\n    /**\r\n     * Overwrites the Cotnainer::show Method\r\n     */\r\n    show() {\r\n        // Update content before show\r\n        super.content(this.getTemplate());\r\n        // Bind default events\r\n        this.defaultEvents();\r\n        // Bind custom events\r\n        this.events();\r\n        // Show step\r\n        super.show();\r\n    }\r\n    /**\r\n     * Register default events\r\n     */\r\n    defaultEvents() {\r\n        var _a, _b, _c, _d;\r\n        // Default button events\r\n        (_a = this.template.querySelector('[data-close]')) === null || _a === void 0 ? void 0 : _a.addEventListener('click', () => this.modal.hide());\r\n        (_b = this.template.querySelector('[data-prev]')) === null || _b === void 0 ? void 0 : _b.addEventListener('click', () => this.modal.prev());\r\n        (_c = this.template.querySelector('[data-next]')) === null || _c === void 0 ? void 0 : _c.addEventListener('click', () => this.modal.next());\r\n        // Default form submit event\r\n        (_d = this.template.querySelector('form')) === null || _d === void 0 ? void 0 : _d.addEventListener('submit', (e) => this.formSubmit(e));\r\n    }\r\n    /**\r\n     * Handle errors\r\n     *\r\n     * @param response\r\n     */\r\n    error(response) {\r\n        // Unlock form\r\n        this.lockedForm = false;\r\n        // Check if there are field errors\r\n        if (response === null || response === void 0 ? void 0 : response.fields) {\r\n            const form = this.template.querySelector('form');\r\n            for (const f in response.fields) {\r\n                // Add error css class\r\n                form[f].parentElement.classList.add('error');\r\n                // Check if the field already has an error text\r\n                if (form[f].nextElementSibling) {\r\n                    // Change error text\r\n                    form[f].nextElementSibling.innerHTML = response.fields[f];\r\n                }\r\n                else {\r\n                    // Add error text\r\n                    const errorText = document.createElement('p');\r\n                    errorText.innerHTML = response.fields[f];\r\n                    form[f].after(errorText);\r\n                }\r\n                // Add event\r\n                form[f].addEventListener('input', () => {\r\n                    form[f].parentElement.classList.remove('error');\r\n                }, { once: true });\r\n            }\r\n        }\r\n    }\r\n    /**\r\n     * Default form submit event to validate and prevent double clicks\r\n     *\r\n     * @protected\r\n     */\r\n    formSubmit(event) {\r\n        event.preventDefault();\r\n        const form = event.target;\r\n        const data = new FormData(form);\r\n        if (!form.checkValidity()) {\r\n            form.reportValidity();\r\n            return;\r\n        }\r\n        if (!this.lockedForm) {\r\n            this.lockedForm = true;\r\n            this.submit(form, data, event);\r\n        }\r\n    }\r\n    /**\r\n     * Set events\r\n     *\r\n     * @protected\r\n     */\r\n    events() { }\r\n    /**\r\n     * Handle form submits\r\n     *\r\n     * @protected\r\n     */\r\n    submit(form, data, event) { }\r\n}\r\nexports[\"default\"] = Step;\r\nStep.stepId = 0;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/components/Step.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/steps/InstallStep.ts":
/*!*********************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/steps/InstallStep.ts ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
eval("\r\nvar __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {\r\n    if (k2 === undefined) k2 = k;\r\n    var desc = Object.getOwnPropertyDescriptor(m, k);\r\n    if (!desc || (\"get\" in desc ? !m.__esModule : desc.writable || desc.configurable)) {\r\n      desc = { enumerable: true, get: function() { return m[k]; } };\r\n    }\r\n    Object.defineProperty(o, k2, desc);\r\n}) : (function(o, m, k, k2) {\r\n    if (k2 === undefined) k2 = k;\r\n    o[k2] = m[k];\r\n}));\r\nvar __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {\r\n    Object.defineProperty(o, \"default\", { enumerable: true, value: v });\r\n}) : function(o, v) {\r\n    o[\"default\"] = v;\r\n});\r\nvar __importStar = (this && this.__importStar) || function (mod) {\r\n    if (mod && mod.__esModule) return mod;\r\n    var result = {};\r\n    if (mod != null) for (var k in mod) if (k !== \"default\" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);\r\n    __setModuleDefault(result, mod);\r\n    return result;\r\n};\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nconst Step_1 = __importDefault(__webpack_require__(/*! ../components/Step */ \"./src/Resources/public/scripts/Installer/components/Step.ts\"));\r\nconst lang_1 = __webpack_require__(/*! ../lang/ */ \"./src/Resources/public/scripts/Installer/lang/index.js\");\r\nconst Process_1 = __importStar(__webpack_require__(/*! ./Process */ \"./src/Resources/public/scripts/Installer/steps/Process/index.ts\"));\r\nconst RegisterProcess_1 = __importDefault(__webpack_require__(/*! ./Process/RegisterProcess */ \"./src/Resources/public/scripts/Installer/steps/Process/RegisterProcess.ts\"));\r\nclass InstallStep extends Step_1.default {\r\n    /**\r\n     * @inheritDoc\r\n     */\r\n    getTemplate() {\r\n        return `\r\n            <h2>${(0, lang_1.i18n)('install.headline')}</h2>\r\n            <div class=\"process\"></div>\r\n            <div class=\"actions\">\r\n                <button data-close disabled>${(0, lang_1.i18n)('actions.close')}</button>\r\n                <button class=\"add primary\" disabled>${(0, lang_1.i18n)('install.actions.add')}</button>\r\n            </div>\r\n        `;\r\n    }\r\n    /**\r\n     * @inheritDoc\r\n     */\r\n    events() {\r\n        // Get the container in which the processes should be appended\r\n        const container = this.template.querySelector('.process');\r\n        const addButton = this.template.querySelector('button.add');\r\n        const closeButton = this.template.querySelector('[data-close]');\r\n        // Method for reset the step\r\n        const resetProcess = () => {\r\n            addButton.disabled = true;\r\n            closeButton.disabled = true;\r\n            this.manager.reset();\r\n        };\r\n        // Create process manager\r\n        this.manager = new Process_1.default();\r\n        // Add processes\r\n        this.manager.addProcess(new Process_1.CheckSystemProcess(container), new RegisterProcess_1.default(container), new Process_1.InstallProcess(container));\r\n        // Register on finish method\r\n        this.manager.finish(() => {\r\n            addButton.disabled = false;\r\n            closeButton.disabled = false;\r\n            closeButton.addEventListener('click', () => {\r\n                // Reset all\r\n                resetProcess();\r\n                this.modal.hide();\r\n            });\r\n            addButton.addEventListener('click', () => {\r\n                // Reset all\r\n                resetProcess();\r\n                this.modal.open(0);\r\n            });\r\n        });\r\n        // Start process manager\r\n        this.manager.start();\r\n    }\r\n}\r\nexports[\"default\"] = InstallStep;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/steps/InstallStep.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/steps/LicenseStep.ts":
/*!*********************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/steps/LicenseStep.ts ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
eval("\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nconst Step_1 = __importDefault(__webpack_require__(/*! ../components/Step */ \"./src/Resources/public/scripts/Installer/components/Step.ts\"));\r\nconst lang_1 = __webpack_require__(/*! ../lang/ */ \"./src/Resources/public/scripts/Installer/lang/index.js\");\r\nconst State_1 = __importDefault(__webpack_require__(/*! ../State */ \"./src/Resources/public/scripts/Installer/State.ts\"));\r\nconst network_1 = __webpack_require__(/*! ../../Utils/network */ \"./src/Resources/public/scripts/Utils/network.js\");\r\nconst Installer_1 = __webpack_require__(/*! ../Installer */ \"./src/Resources/public/scripts/Installer/Installer.ts\");\r\nclass LicenseStep extends Step_1.default {\r\n    /**\r\n     * @inheritDoc\r\n     */\r\n    getTemplate() {\r\n        return `\r\n            <h2>${(0, lang_1.i18n)('license.headline')}</h2>\r\n            <p>${(0, lang_1.i18n)('license.description')}</p>\r\n            <form id=\"license-form\" autocomplete=\"off\">\r\n                <div class=\"widget\">\r\n                    <label for=\"license\">${(0, lang_1.i18n)('license.form.label.license')}</label>\r\n                    <input type=\"text\" name=\"license\" id=\"license\" placeholder=\"XXXX-XXXX-XXXX-XXXX-XXXX\" autocomplete=\"off\" required/>\r\n                </div>\r\n            </form>\r\n            <div class=\"actions\">\r\n                <button data-close>${(0, lang_1.i18n)('actions.close')}</button>\r\n                <button type=\"submit\" form=\"license-form\" class=\"check primary\">${(0, lang_1.i18n)('license.actions.next')}</button>\r\n            </div>\r\n        `;\r\n    }\r\n    /**\r\n     * @inheritDoc\r\n     */\r\n    submit(form, data) {\r\n        // Save license form data\r\n        State_1.default.set('license', data.get('license'));\r\n        // Show loader\r\n        this.modal.loader(true, (0, lang_1.i18n)('license.loading'));\r\n        // Check license\r\n        (0, network_1.call)(Installer_1.routes.license, {\r\n            license: data.get('license')\r\n        }).then((response) => {\r\n            // Hide loader\r\n            this.modal.loader(false);\r\n            // Check errors\r\n            if (response.error) {\r\n                super.error(response);\r\n                return;\r\n            }\r\n            // Save product information\r\n            State_1.default.set('product', response);\r\n            // Reset form\r\n            form.reset();\r\n            // Unlock form\r\n            this.lockedForm = false;\r\n            // Show next step\r\n            this.modal.next();\r\n        }).catch(() => {\r\n            // ToDo: Error\r\n            console.log('error catch');\r\n            // Unlock form\r\n            this.lockedForm = false;\r\n        });\r\n    }\r\n}\r\nexports[\"default\"] = LicenseStep;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/steps/LicenseStep.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/steps/Process/CheckSystemProcess.ts":
/*!************************************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/steps/Process/CheckSystemProcess.ts ***!
  \************************************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
eval("\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nconst Process_1 = __importDefault(__webpack_require__(/*! ./Process */ \"./src/Resources/public/scripts/Installer/steps/Process/Process.ts\"));\r\nconst lang_1 = __webpack_require__(/*! ../../lang/ */ \"./src/Resources/public/scripts/Installer/lang/index.js\");\r\nconst Installer_1 = __webpack_require__(/*! ../../Installer */ \"./src/Resources/public/scripts/Installer/Installer.ts\");\r\nconst network_1 = __webpack_require__(/*! ../../../Utils/network */ \"./src/Resources/public/scripts/Utils/network.js\");\r\nclass CheckSystemProcess extends Process_1.default {\r\n    /**\r\n     * @inheritDoc\r\n     */\r\n    getTemplate() {\r\n        return `\r\n            <div data-loader></div>\r\n            <div class=\"content\">\r\n                <div class=\"title\">${(0, lang_1.i18n)('install.systemcheck.title')}</div>\r\n                <p>${(0, lang_1.i18n)('install.systemcheck.description')}</p>\r\n            </div>\r\n        `;\r\n    }\r\n    /**\r\n     * @inheritDoc\r\n     */\r\n    process() {\r\n        // Check license\r\n        (0, network_1.call)(Installer_1.routes.systemcheck).then((response) => {\r\n            console.log(response);\r\n            // Check errors\r\n            if (response.error) {\r\n                this.reject(response);\r\n                return;\r\n            }\r\n            this.resolve();\r\n        }).catch((e) => this.reject(e));\r\n    }\r\n    /**\r\n     * @inheritDoc\r\n     */\r\n    reject(data) {\r\n        super.reject(data);\r\n        // Exit manager and following processes\r\n        this.manager.exit();\r\n    }\r\n}\r\nexports[\"default\"] = CheckSystemProcess;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/steps/Process/CheckSystemProcess.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/steps/Process/InstallProcess.ts":
/*!********************************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/steps/Process/InstallProcess.ts ***!
  \********************************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
eval("\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nconst Process_1 = __importDefault(__webpack_require__(/*! ./Process */ \"./src/Resources/public/scripts/Installer/steps/Process/Process.ts\"));\r\nconst lang_1 = __webpack_require__(/*! ../../lang/ */ \"./src/Resources/public/scripts/Installer/lang/index.js\");\r\nclass InstallProcess extends Process_1.default {\r\n    /**\r\n     * @inheritDoc\r\n     */\r\n    getTemplate() {\r\n        return `\r\n            <div data-loader></div>\r\n            <div class=\"content\">\r\n                <div class=\"title\">${(0, lang_1.i18n)('install.install.title')}</div>\r\n                <p>${(0, lang_1.i18n)('install.install.description')}</p>\r\n            </div>\r\n        `;\r\n    }\r\n    /**\r\n     * @inheritDoc\r\n     */\r\n    process() {\r\n        setTimeout(() => {\r\n            console.log('Install done');\r\n            this.resolve();\r\n        }, 5500);\r\n    }\r\n}\r\nexports[\"default\"] = InstallProcess;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/steps/Process/InstallProcess.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/steps/Process/Process.ts":
/*!*************************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/steps/Process/Process.ts ***!
  \*************************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
eval("\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nconst Loader_1 = __importDefault(__webpack_require__(/*! ../../components/Loader */ \"./src/Resources/public/scripts/Installer/components/Loader.ts\"));\r\nconst Container_1 = __importDefault(__webpack_require__(/*! ../../components/Container */ \"./src/Resources/public/scripts/Installer/components/Container.ts\"));\r\nclass Process extends Container_1.default {\r\n    constructor(container) {\r\n        // Create container\r\n        super('process' + Process.processId++);\r\n        this.container = container;\r\n        // Create process step template\r\n        this.addClass('process-step', 'not-active');\r\n        this.content(this.getTemplate());\r\n        this.appendTo(this.container);\r\n        // Add error message container\r\n        this.errorContainer = document.createElement('div');\r\n        this.errorContainer.classList.add('errors');\r\n        this.template.append(this.errorContainer);\r\n        // Add loader\r\n        const loaderContainer = this.template.querySelector('[data-loader]');\r\n        if (loaderContainer) {\r\n            this.loader = new Loader_1.default();\r\n            this.loader.show();\r\n            this.loader.pause();\r\n            this.loader.appendTo(loaderContainer);\r\n        }\r\n        this.mount();\r\n    }\r\n    /**\r\n     * Bind a manager instance to a process step\r\n     *\r\n     * @param manager\r\n     */\r\n    addManager(manager) {\r\n        this.manager = manager;\r\n    }\r\n    /**\r\n     * Reset process\r\n     */\r\n    reset() {\r\n        var _a, _b, _c;\r\n        this.addClass('not-active');\r\n        (_a = this.loader) === null || _a === void 0 ? void 0 : _a.pause();\r\n        (_b = this.loader) === null || _b === void 0 ? void 0 : _b.removeClass('done', 'fail', 'pause');\r\n        (_c = this.template.querySelector('div.errors')) === null || _c === void 0 ? void 0 : _c.remove();\r\n    }\r\n    /**\r\n     * Starts a single process\r\n     */\r\n    start() {\r\n        var _a;\r\n        (_a = this.loader) === null || _a === void 0 ? void 0 : _a.play();\r\n        this.removeClass('not-active');\r\n        // Start process\r\n        this.process();\r\n    }\r\n    /**\r\n     * Resolve process\r\n     */\r\n    resolve() {\r\n        var _a, _b;\r\n        (_a = this.loader) === null || _a === void 0 ? void 0 : _a.pause();\r\n        (_b = this.loader) === null || _b === void 0 ? void 0 : _b.addClass('done');\r\n        // Start next process\r\n        this.manager.next();\r\n    }\r\n    /**\r\n     * Reject process\r\n     *\r\n     * @param data\r\n     */\r\n    reject(data) {\r\n        var _a, _b;\r\n        (_a = this.loader) === null || _a === void 0 ? void 0 : _a.pause();\r\n        (_b = this.loader) === null || _b === void 0 ? void 0 : _b.addClass('fail');\r\n        this.error(data);\r\n    }\r\n    /**\r\n     * Shows occurred errors in the process\r\n     */\r\n    error(data) {\r\n        // Check for messages of intercepted errors\r\n        if (data === null || data === void 0 ? void 0 : data.messages) {\r\n            for (const text of data.messages) {\r\n                this.addErrorParagraph(text);\r\n            }\r\n        }\r\n        // Check whether a fatal error has occurred.\r\n        // For example, no connection could be established to the server\r\n        if (data === null || data === void 0 ? void 0 : data.message) {\r\n            this.addErrorParagraph(data.message);\r\n        }\r\n    }\r\n    /**\r\n     * Adds a paragraph to the error container\r\n     *\r\n     * @param content\r\n     */\r\n    addErrorParagraph(content) {\r\n        const msg = document.createElement('p');\r\n        msg.innerText = content;\r\n        this.errorContainer.append(msg);\r\n    }\r\n    /**\r\n     * Pause process\r\n     */\r\n    pause() {\r\n        var _a, _b;\r\n        (_a = this.loader) === null || _a === void 0 ? void 0 : _a.pause();\r\n        (_b = this.loader) === null || _b === void 0 ? void 0 : _b.addClass('pause');\r\n    }\r\n    /**\r\n     * Allows manipulation for process specific properties\r\n     */\r\n    mount() { }\r\n}\r\nexports[\"default\"] = Process;\r\nProcess.processId = 0;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/steps/Process/Process.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/steps/Process/ProcessManager.ts":
/*!********************************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/steps/Process/ProcessManager.ts ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
eval("\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nclass ProcessManager {\r\n    constructor() {\r\n        /**\r\n         * All processes to be processed\r\n         *\r\n         * @private\r\n         */\r\n        this.processes = [];\r\n        /**\r\n         * Method which is called when all processes have been completed\r\n         */\r\n        this.onFinish = () => { };\r\n    }\r\n    /**\r\n     * Adds one or more processes to be queued\r\n     *\r\n     * @param process\r\n     */\r\n    addProcess(...process) {\r\n        for (const proc of process) {\r\n            // Bind manager instance\r\n            proc.addManager(this);\r\n            // Add process to queue\r\n            this.processes.push(proc);\r\n        }\r\n        return this;\r\n    }\r\n    /**\r\n     * Starts the execution of all processes\r\n     *\r\n     * @param startIndex\r\n     */\r\n    start(startIndex = 0) {\r\n        if (startIndex >= this.processes.length) {\r\n            this.exit();\r\n            return;\r\n        }\r\n        this.currentIndex = startIndex;\r\n        this.currentProcess = this.processes[this.currentIndex];\r\n        this.currentProcess.start();\r\n    }\r\n    /**\r\n     * Call the finish method\r\n     */\r\n    exit() {\r\n        this.onFinish.call(this);\r\n    }\r\n    /**\r\n     * Starts the next process\r\n     */\r\n    next() {\r\n        this.start(++this.currentIndex);\r\n    }\r\n    /**\r\n     * Calling the registered method when all processes are finished\r\n     *\r\n     * @param fn\r\n     */\r\n    finish(fn) {\r\n        this.onFinish = fn;\r\n        return this;\r\n    }\r\n    /**\r\n     * Reset manager and all processes\r\n     */\r\n    reset() {\r\n        this.currentIndex = 0;\r\n        for (const proc of this.processes) {\r\n            proc.reset();\r\n        }\r\n    }\r\n}\r\nexports[\"default\"] = ProcessManager;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/steps/Process/ProcessManager.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/steps/Process/RegisterProcess.ts":
/*!*********************************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/steps/Process/RegisterProcess.ts ***!
  \*********************************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
eval("\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nconst Process_1 = __importDefault(__webpack_require__(/*! ./Process */ \"./src/Resources/public/scripts/Installer/steps/Process/Process.ts\"));\r\nconst lang_1 = __webpack_require__(/*! ../../lang/ */ \"./src/Resources/public/scripts/Installer/lang/index.js\");\r\nclass RegisterProcess extends Process_1.default {\r\n    /**\r\n     * @inheritDoc\r\n     */\r\n    getTemplate() {\r\n        return `\r\n            <div data-loader></div>\r\n            <div class=\"content\">\r\n                <div class=\"title\">${(0, lang_1.i18n)('install.register.title')}</div>\r\n                <p>${(0, lang_1.i18n)('install.register.description')}</p>\r\n            </div>\r\n        `;\r\n    }\r\n    /**\r\n     * @inheritDoc\r\n     */\r\n    process() {\r\n        setTimeout(() => {\r\n            console.log('Product registration done');\r\n            this.resolve();\r\n        }, 3000);\r\n    }\r\n}\r\nexports[\"default\"] = RegisterProcess;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/steps/Process/RegisterProcess.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/steps/Process/index.ts":
/*!***********************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/steps/Process/index.ts ***!
  \***********************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
eval("\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nexports.InstallProcess = exports.CheckSystemProcess = void 0;\r\nconst ProcessManager_1 = __importDefault(__webpack_require__(/*! ./ProcessManager */ \"./src/Resources/public/scripts/Installer/steps/Process/ProcessManager.ts\"));\r\nconst CheckSystemProcess_1 = __importDefault(__webpack_require__(/*! ./CheckSystemProcess */ \"./src/Resources/public/scripts/Installer/steps/Process/CheckSystemProcess.ts\"));\r\nexports.CheckSystemProcess = CheckSystemProcess_1.default;\r\nconst InstallProcess_1 = __importDefault(__webpack_require__(/*! ./InstallProcess */ \"./src/Resources/public/scripts/Installer/steps/Process/InstallProcess.ts\"));\r\nexports.InstallProcess = InstallProcess_1.default;\r\nexports[\"default\"] = ProcessManager_1.default;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/steps/Process/index.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/steps/ProductStep.ts":
/*!*********************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/steps/ProductStep.ts ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
eval("\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nconst Step_1 = __importDefault(__webpack_require__(/*! ../components/Step */ \"./src/Resources/public/scripts/Installer/components/Step.ts\"));\r\nconst lang_1 = __webpack_require__(/*! ../lang/ */ \"./src/Resources/public/scripts/Installer/lang/index.js\");\r\nconst State_1 = __importDefault(__webpack_require__(/*! ../State */ \"./src/Resources/public/scripts/Installer/State.ts\"));\r\nclass ProductStep extends Step_1.default {\r\n    /**\r\n     * @inheritDoc\r\n     */\r\n    getTemplate() {\r\n        const props = State_1.default.get('product');\r\n        let products = '';\r\n        for (const product of props.products) {\r\n            const image = product.image ? `<img src=\"${product.image}\" alt/>` : '';\r\n            products += `\r\n                 <div class=\"product\">\r\n                    <div class=\"image\">\r\n                        ${image}\r\n                    </div>\r\n                    <div class=\"content\">\r\n                        <div class=\"title\">${product.name}</div>\r\n                        <div class=\"description\">${product.description}</div>\r\n                        <div class=\"version\">${product.version}</div>\r\n                    </div>\r\n                </div>\r\n            `;\r\n        }\r\n        return `\r\n            <h2>${(0, lang_1.i18n)('product.headline')}</h2>\r\n            <div class=\"products\">\r\n                ${products}\r\n            </div>\r\n            <div class=\"actions\">\r\n                <button data-prev>${(0, lang_1.i18n)('actions.back')}</button>\r\n                <button data-next class=\"primary\">${(0, lang_1.i18n)('product.actions.install')}</button>\r\n            </div>\r\n        `;\r\n    }\r\n}\r\nexports[\"default\"] = ProductStep;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/steps/ProductStep.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/steps/index.ts":
/*!***************************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/steps/index.ts ***!
  \***************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
eval("\r\nvar __importDefault = (this && this.__importDefault) || function (mod) {\r\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\r\n};\r\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\r\nexports.InstallStep = exports.ProductStep = exports.LicenseStep = void 0;\r\nconst LicenseStep_1 = __importDefault(__webpack_require__(/*! ./LicenseStep */ \"./src/Resources/public/scripts/Installer/steps/LicenseStep.ts\"));\r\nexports.LicenseStep = LicenseStep_1.default;\r\nconst ProductStep_1 = __importDefault(__webpack_require__(/*! ./ProductStep */ \"./src/Resources/public/scripts/Installer/steps/ProductStep.ts\"));\r\nexports.ProductStep = ProductStep_1.default;\r\nconst InstallStep_1 = __importDefault(__webpack_require__(/*! ./InstallStep */ \"./src/Resources/public/scripts/Installer/steps/InstallStep.ts\"));\r\nexports.InstallStep = InstallStep_1.default;\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/steps/index.ts?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/lang/de.js":
/*!***********************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/lang/de.js ***!
  \***********************************************************/
/***/ ((module) => {

eval("module.exports = {\r\n    // Global\r\n    \"actions.close\":                    \"Schließen\",\r\n    \"actions.back\":                     \"Zurück\",\r\n\r\n    // License step\r\n    \"license.headline\":                 \"Produkt registrieren\",\r\n    \"license.description\":              \"Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.\",\r\n    \"license.form.label.license\":       \"Produktlizenz\",\r\n    \"license.actions.next\":             \"Produktlizenz überprüfen\",\r\n    \"license.loading\":                  \"Produkte werden abgerufen\",\r\n\r\n    // Product step\r\n    \"product.headline\":                 \"Produkt installieren\",\r\n    \"product.actions.install\":          \"Produkt installieren\",\r\n\r\n    // Install step\r\n    \"install.headline\":                 \"Installation wird durchgeführt\",\r\n    \"install.actions.add\":              \"Weiteres Produkt registrieren\",\r\n\r\n    'install.systemcheck.title':        \"Systemüberprüfung\",\r\n    'install.systemcheck.description':  \"Vor der Installation werden alle Anforderungen sowie Abhängigkeiten im System überprüft.\",\r\n\r\n    'install.register.title':           \"Produktregistrierung\",\r\n    'install.register.description':     \"Die Domain wird für das ausgewählte Produkt registriert.\",\r\n\r\n    'install.install.title':            \"Produktinstallation\",\r\n    'install.install.description':      \"Das Produkt wird im System installiert und in zur Verfügung gestellt.\",\r\n}\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/lang/de.js?");

/***/ }),

/***/ "./src/Resources/public/scripts/Installer/lang/en.js":
/*!***********************************************************!*\
  !*** ./src/Resources/public/scripts/Installer/lang/en.js ***!
  \***********************************************************/
/***/ ((module) => {

eval("module.exports = {\r\n    // Global\r\n    \"actions.close\":                    \"Close\",\r\n    \"actions.back\":                     \"Back\",\r\n\r\n    // License step\r\n    \"license.headline\":                 \"Register product\",\r\n    \"license.description\":              \"Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.\",\r\n    \"license.form.label.license\":       \"Product license\",\r\n    \"license.actions.next\":             \"Check license\",\r\n    \"license.loading\":                  \"Products are retrieved\",\r\n\r\n    // Product step\r\n    \"product.headline\":                 \"Install product\",\r\n    \"product.actions.install\":          \"Install product\",\r\n\r\n    // Install step\r\n    \"install.headline\":                 \"Installation\",\r\n    \"install.actions.add\":              \"Register more products\",\r\n\r\n    'install.systemcheck.title':        \"System check\",\r\n    'install.systemcheck.description':  \"Before installation, all requirements as well as dependencies in the system are checked.\",\r\n\r\n    'install.register.title':           \"Product registration\",\r\n    'install.register.description':     \"The domain is registered for the selected product.\",\r\n\r\n    'install.install.title':            \"Product installation\",\r\n    'install.install.description':      \"The product is installed in the system and put into operation.\",\r\n}\r\n\n\n//# sourceURL=webpack://@oveleon/product-installer/./src/Resources/public/scripts/Installer/lang/en.js?");

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