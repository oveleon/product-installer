# Aufbau von Bridges

!> Wenn du noch nicht weißt, wie Bridges funktionieren oder wofür diese nötig sind, lese vorher bitte [diesen Artikel](bridges/README.md)!

Der Produkt Installer stellt den Bridges eine breite Palette an Eingabemasken und Prozessen zur Verfügung. Diese dienen als Grundlage für die Konfiguration und Steuerung des Installationsablaufs. Die Bridges nutzen diese Masken und Prozesse, um dem Produkt-Installer mitzuteilen, was zu tun ist und welche spezifischen Schritte und Masken während des Installationsprozesses ausgeführt werden sollen.

Eine Bridge fungiert als Vermittler zwischen der API des Shop-Systems ([License-Connector](connector/README.md)) sowie dem Produkt Installer selbst. Sie stellt sicher, dass die Kommunikation reibungslos verläuft und alle erforderlichen Informationen zwischen den beiden Systemen ausgetauscht werden können.

Es ist wichtig zu beachten, dass der Produkt Installer zu Beginn bereits automatisch eine Bridge installiert, um lokale Pakete zu installieren. Dadurch wird sichergestellt, dass der Produkt-Installer von Anfang an einsatzbereit ist und lokal gespeicherte Produktpakete problemlos installiert werden können. Dadurch können Sie Ihren Kunden auch Produkte auf verschiedene Art und Weise zukommen lassen, ohne überhaupt ein Shop-System betreiben zu müssen.
