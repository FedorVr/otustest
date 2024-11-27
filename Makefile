b:
	docker compose build

up:
	docker compose up -d

d:
	docker compose down --remove-orphans

sh:
	docker exec -it otus-php-1 sh

ps:
	docker compose ps

dbc:
	docker exec otus-postgresql-1 psql -U user -d skill-mentor-service

rml:
	rm -rf ./var/log/*