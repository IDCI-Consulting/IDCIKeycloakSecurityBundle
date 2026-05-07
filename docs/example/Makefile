# Variables
source_tag = latest
stack_name=keycloak

keycloak_container_id = $(shell docker ps --filter name="$(stack_name)_keycloak" -q)

.PHONY: export
export:
	docker exec -it $(keycloak_container_id) keycloak/bin/standalone.sh -Dkeycloak.migration.action=export \
		-Dkeycloak.migration.provider=singleFile \
		-Dkeycloak.migration.realmName=$(realm) \
		-Dkeycloak.migration.usersExportStrategy=REALM_FILE \
		-Dkeycloak.migration.file=/tmp/realm.json \
		-Djboss.http.port=8888 -Djboss.https.port=9999 -Djboss.management.http.port=7777
	docker cp $(keycloak_container_id):/tmp/realm.json ./import/$(realm)-realm.json

.PHONY: import
import:
	docker cp ./import/$(realm)-realm.json $(keycloak_container_id):/tmp/realm.json
	docker exec -it $(keycloak_container_id) keycloak/bin/standalone.sh -Dkeycloak.migration.action=import \
		-Dkeycloak.migration.provider=singleFile \
		-Dkeycloak.migration.file=/tmp/realm.json \
		-Dkeycloak.import=/tmp/realm.json \
		-Dkeycloak.migration.strategy=OVERWRITE_EXISTING \
		-Djboss.http.port=8888 -Djboss.https.port=9999 -Djboss.management.http.port=7777

.PHONY: build-images
build-images:
	docker build -t local/keycloak:$(source_tag) -f .docker/Dockerfile .

.PHONY: deploy
deploy: build-images
	docker stack deploy -c docker-compose.yml $(stack_name)

.PHONY: undeploy
undeploy:
	docker stack rm $(stack_name)
