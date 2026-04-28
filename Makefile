ACT_IMAGE ?= efrecon/act:v0.2.80
WORKDIR   ?= /work
UID_GID    := $(shell id -u):$(shell id -g)
DOCKER_GID := $(shell stat -c '%g' /var/run/docker.sock)

PLATFORM  ?= -P ubuntu-latest=ghcr.io/catthehacker/ubuntu:act-24.04

# Secrets-File in ENV format
SECRETS   ?= --secret-file .secrets

ACT_ARGS ?= \
  --artifact-server-path /home/act/.cache/artifacts

define DOCKER_RUN
docker run --rm -it \
  -u $(UID_GID) \
  --group-add $(DOCKER_GID) \
  -e HOME=/home/act \
  -e XDG_CACHE_HOME=/home/act/.cache \
  -e ACT_CACHE_DIR=/home/act/.cache/actcache \
  -v /var/run/docker.sock:/var/run/docker.sock \
  -v $(PWD):$(WORKDIR) -w $(WORKDIR) \
  -v $$HOME/.cache:/home/act/.cache \
  $(ACT_IMAGE)
endef

# ---- Targets ----
.PHONY: all ci clean

all: ci clean

ci:   ## Standard-Event "push"
	$(DOCKER_RUN) $(PLATFORM) $(SECRETS) $(ACT_ARGS) 2>&1 | tee /tmp/act-output.log; \
	echo ""; \
	echo "=== 🏁 Summary ==="; \
	grep "🏁" /tmp/act-output.log || true


clean:
	docker rm -f $$(docker ps -aq --filter "name=act-") 2>/dev/null || true

