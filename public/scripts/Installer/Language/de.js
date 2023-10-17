module.exports = {
    // Error
    "error.unknown":                                "Oops, die Abfrage konnte nicht durchgeführt werden. Bitte versuchen Sie es erneut!",
    "error.default":                                "Ein Fehler ist aufgetreten!",
    "error.session.lost":                           "Ihre Sitzung ist abgelaufen, bitte loggen Sie sich erneut ein!",

    // Warnings
    "warning.cancel.setup":                         "Sie sind dabei, die Einrichtung abzubrechen, wollen Sie die Seite wirklich verlassen?",

    // Global
    "actions.close":                                "Schließen",
    "actions.back":                                 "Zurück",
    "actions.next":                                 "Weiter",
    "actions.start":                                "Starten",
    "actions.products":                             "Produktübersicht",
    "actions.setup":                                "Schnelle Einrichtung starten",
    "actions.setup.expert":                         'Im Expertenmodus starten <small><sup style="display: inline-block; color: #ea7777; font-weight: 600; margin-right: 9px;">BETA</sup></small>',

    "actions.console.toggle":                       'Konsole öffnen',
    "actions.database.skip":                        'Überspringen und fortfahren',
    "actions.database.migrate":                     'Datenbank migrieren',

    "type.product":                                 "Produkt",
    "type.package":                                 "Produktpaket",

    "global.yes":                                   "Ja",
    "global.no":                                    "Nein",

    // Product
    "product.badge.registered":                     "Registriert",
    "product.badge.removed":                        "Entfernt",
    "product.setup":                                "Produkt einrichten",
    "product.update":                               "Produkt aktualisieren",
    "product.info":                                 "Produktinformation",
    "product.remove":                               "Aus der Liste entfernen",
    "product.no_version":                           "Das Produkt steht leider nicht für Ihre Contao-Version zur Verfügung. Bitte aktualisieren Sie Contao.",

    "product.loading.remove":                       "Das Produkt wird entfernt",

    "product.label.title":                          "Titel",
    "product.label.description":                    "Beschreibung",
    "product.label.version":                        "Aktuelle Version",
    "product.label.latestVersion":                  "Neuste Version",
    "product.label.registered":                     "Produkt ist registriert",
    "product.label.registeredDate":                 "Registriert am",
    "product.label.shop":                           "Erworben bei",

    // Dashboard step
    "dashboard.headline":                           "Produktübersicht",
    "dashboard.noProducts":                         "Sie haben noch keine Produkte für dieses Projekt registriert.",
    "dashboard.actions.register":                   "Produkt registrieren",
    "dashboard.loading":                            "Produkte werden abgerufen",
    "dashboard.toggle.darkLight":                   "Hell / Dunkel Modus",
    "dashboard.toggle.fullscreen":                  "Vollbild",

    // Upload step
    "upload.headline":                              "Produkt hochladen",
    "upload.description":                           "Bitte laden Sie ein Produkt hoch. Entweder Sie ziehen die Produktdatei (.content) in den gestrichelten Bereich oder klicken auf diesen um den Dateimanager aufzurufen.",
    "upload.loading":                               "Produkt wird hochgeladen",

    // License Connector step
    "license_connector.headline":                   "Schittstelle wählen",
    "license_connector.load.connector":             "Schnittstellen werden abgerufen",
    "license_connector.load.steps":                 "Masken werden geladen",
    "license_connector.load.redirect":              "Einen kleinen Moment noch, Sie werden gleich weitergeleitet",

    // Contao Manager
    "contao_manager.headline":                      "Contao Manager",
    "contao_manager.description":                   "Damit der Product Installer die erforderlichen Abhängigkeiten installieren kann, wird der Zugriff auf den Contao Manager benötigt. Bitte klicken Sie auf \"Autorisieren\" und folgen Sie den weiteren Schritten im Contao Manager. Sobald die Autorisierung abgeschlossen ist, werden Sie zum Installationsprozess zurückgeleitet.",
    "contao_manager.description.notInstalled":      "Damit der Product Installer die erforderlichen Abhängigkeiten installieren kann, benötigt dieser Zugriff auf den Contao Manager. Um fortzufahren installieren Sie bitte den Contao Manager und starten Sie den Installationsprozess erneut.",
    "contao_manager.description.success":           "Der Zugriff auf den Contao Manager wurde erfolgreich autorisiert. Sie können nun mit dem Registrierungsprozess fortfahren.",
    "contao_manager.authorize":                     "Autorisieren",
    "contao_manager.open_manager":                  "Contao Manager öffnen",
    "contao_manager.install.label":                 "Abhängigkeiten manuell installieren",
    "contao_manager.install.button":                "Manuelle Installation",
    "contao_manager.install.description":           "Ihre composer.json Datei wurde für die manuelle Installation vorbereitet. Bitte stellen Sie sicher, dass Sie vor dem nächsten Schritt folgende Abhängigkeiten installiert haben.",
    "contao_manager.dependencies.headline":         "Abhängigkeiten",
    "contao_manager.dependencies.installed":        "Ja, ich habe alle Abhängigkeiten installiert",
    "contao_manager.connection.active":             "Verbindung wurde erfolreich hergestellt",
    "contao_manager.connection.inactive":           "Verbindung konnte nicht hergestellt werden",
    "contao_manager.connection.notInstalled":       "Contao Manager muss installiert werden",
    "contao_manager.loading":                       "Verbindung zum Contao Manager wird hergestellt",
    "contao_manager.loading.composer":              "Die composer.json Datei wird für die manuelle Installation vorbereitet",
    "contao_manager.process.title":                 "Systemvorbereitung",
    "contao_manager.process.description":           "Die für das Produkt notwendigen Abhängigkeiten werden geprüft und wenn nötig installiert.",

    // Setup step
    "setup.headline":                               "Produkteinrichtung",
    "setup.prompt.headline":                        "Produkteinrichtung",
    "setup.available_imports.headline":             "Verfügbare Datenpakete in diesem Produkt",
    "setup.loading":                                "Produkteinrichtung wird vorbereitet",
    "setup.loading.step":                           "Produkteinrichtung wird durchgeführt",
    "setup.complete":                               "Produkteinrichtung erfolgreich abgeschlossen",

    // License step
    "license.headline":                             "Lizenzüberprüfung",
    "license.description":                          "Geben Sie hier Ihren Lizenzschlüssel für das zu installierende Produkt an. Im nächsten Schritt können Sie die zugehörigen Produkte sichten sowie installieren oder updaten.",
    "license.form.label.license":                   "Produktlizenz",
    "license.form.desc.license":                    "Bitte geben Sie hier Ihre Produktlizenz ein.",
    "license.actions.next":                         "Produktlizenz überprüfen",
    "license.loading":                              "Produkte werden abgerufen",

    // Product step
    "product.headline":                             "Zu registrierende Produkte",

    // Install step
    "install.headline":                             "Installation",
    "install.actions.add":                          "Weiteres Produkt registrieren",

    // Advertising step
    "advertising.doNotShowAgain":                   "Nicht erneut anzeigen",

    // Manager process
    "process.contao_manager.title":                 "Contao Manager",
    "process.contao_manager.download.title":        "Pakete herunterladen",
    "process.contao_manager.download.description":  "Abhängige Pakete werden heruntergeladen und zur Verfügung gestellt.",

    "process.contao_manager.package.title":         "Pakete integrieren",
    "process.contao_manager.package.description":   "Pakete werden dem Projekt hinterlegt.",

    "process.contao_manager.composer.title":        "Abhängigkeiten installieren",
    "process.contao_manager.composer.description":  "Die Abhängigkeiten werden über Composer installiert.",

    "process.contao_manager.database.title":        "Datenbank & Migrationen",
    "process.contao_manager.database.description":  "Überprüfe die Datenbank auf Änderungen.",

    // Download process
    "process.download.title":                       "Produkte abrufen",
    "process.download.description":                 "Die Produkte werden abgerufen und heruntergeladen.",

    // Register product process
    "process.register.title":                       "Produktregistrierung",
    "process.register.description":                 "Die installierten Produkte werden registriert.",

    // Composer process
    "process.composer.console.title":               "Abhängigkeiten installieren",
    "process.composer.running.try.title":           "Der Contao Manager führt derzeit eine andere Aufgabe durch.",
    "process.composer.running.try.timer":           "Versuche erneut in <b>#seconds# Sekunden</b>.",
    "process.composer.running.stop.title":          "Nicht beendete Aufgaben werden beendet.",

    // Database process
    "process.database.console.title":               "Datenbank aktualisieren",
    "process.database.deletionHint":                "Bitte beachten Sie, dass der Product Installer keine Löschungen durchführt. Öffnen Sie bitte den Contao Manager um Löschungen manuell durchzuführen.",

    // Tasks
    "task.label.setup":                             "Einrichten",
    "task.content_package.title":                   "Inhaltspaket",
    "task.content_package.description":             "Inhaltspakete befüllen Ihre Contao-Instanz mit Leben. Dabei kann es sich um Seiten, Artikel, Module, Content-Elemente usw. handeln. Nutzen Sie den Expertenmodus, um einzelne Inhalte vom Import auszuschließen.",
    "task.package_valid.true":                      "Bereit",
    "task.package_valid.false":                     "Fehlende Abhängigkeiten",

    // Form
    "form.field.tables.label":                      "Zu importierenden Inhalte",
    "form.field.tables.desc":                       "Bitte wählen Sie hier die zu importierenden Tabellen.",

    "form.field.rootPage.label":                    "Seitenstartpunkt",
    "form.field.rootPage.desc":                     "Bitte definieren Sie, wo die neue Seitenstruktur eingebettet werden soll.",

    "form.field.files.browse":                      "Dateien durchsuchen",
    "form.field.popup.title":                       "Feldinformationen",
}
