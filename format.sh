#!/bin/sh

./add-copyright-notice.php
~/Software/phpstorm/bin/phpstorm.sh format -r src/ -s CodingStyle.xml
~/Software/phpstorm/bin/phpstorm.sh format -r tests/ -s CodingStyle.xml
