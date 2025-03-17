build:
	docker build -t bracket .
run:
	docker run -v ./:/usr/src/myapp -e XDEBUG_TRIGGER=1 -e PHP_IDE_CONFIG="serverName=bracket" bracket php generate_bracket.php $(number)
rm:
	docker ps -aq --filter ancestor=bracket | grep -q . && docker rm -f $(docker ps -aq --filter ancestor=bracket) || echo "No containers to remove"
	docker images -q bracket | grep -q . && docker rmi -f bracket || echo "No image to remove"