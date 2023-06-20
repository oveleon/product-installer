# Aufgaben und Aktionen

Der Product-Installer unterstützt verschiedene Arten von Aufgaben, die ein Produkt bereitstellen kann. Aktuell sind drei Arten von Aufgaben implementiert, wobei es wichtig zu beachten ist, dass ein Produkt unendlich viele Aufgaben enthalten kann. Die folgenden vier Arten von Aufgaben stehen zur Verfügung:

1. **Composer-Abhängigkeiten installieren (composer_update):**<br/>Diese Aufgabe ermöglicht die Installation von Composer-Abhängigkeiten, die das Produkt benötigt. Der Product-Installer führt den notwendigen Composer-Befehl aus, um die Abhängigkeiten herunterzuladen und zu installieren. Dadurch wird sichergestellt, dass alle erforderlichen Abhängigkeiten korrekt eingerichtet sind.

2. **Contao-Manager-Artefakte installieren (manager_package):**<br/>Mit dieser Aufgabe können Artefakte des Contao-Managers installiert werden. Der Product-Installer lädt die entsprechenden Pakete herunter und installiert sie über den Contao-Manager. Dadurch können zusätzliche Funktionen oder Erweiterungen für das Produkt bereitgestellt werden, die über den Contao-Manager verfügbar sind.

3. **Dateien oder Repository klonen (repo_import):**<br/>Diese Aufgabe ermöglicht das Klonen von Dateien oder Repositorys von einem Server oder Versionsverwaltungsprogramm wie GitHub. Der Product-Installer lädt die entsprechenden Dateien oder Repositorys herunter und legt sie im System ab. Dadurch können spezifische Dateien oder externe Quellen in das Produkt integriert werden.

Die verschiedenen Arten von Aufgaben im Product-Installer bieten Flexibilität und ermöglichen es, unterschiedliche Aktionen während der Installation und Einrichtung von Produkten durchzuführen. Durch die Kombination und Anpassung dieser Aufgaben können Produkte auf maßgeschneiderte Weise bereitgestellt werden, um den spezifischen Anforderungen und Funktionalitäten gerecht zu werden.
