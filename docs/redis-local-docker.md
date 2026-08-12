# Redis local — VISERVICE

## Dependencias

| Qué | Para qué |
|-----|----------|
| **Docker Desktop** | Contenedor Redis |
| **Extensión PHP `redis` (phpredis)** | Cliente Redis en Laravel (`REDIS_CLIENT=phpredis`) |

Laravel ya trae soporte Redis en el framework; no hace falta paquete Composer si usas **phpredis**.

**Alternativa:** `composer require predis/predis` y en `.env` → `REDIS_CLIENT=predis` (sin extensión PHP).

---

## ¿Las tengo?

```powershell
php -m | findstr -i redis
php -r "echo extension_loaded('redis') ? 'phpredis: si' : 'phpredis: no';"
docker --version
```

- `phpredis: si` → extensión OK.
- Docker debe responder con versión.

---

## Instalar lo que falte

### phpredis (Laragon)

1. Laragon → **Menu → PHP → Extensions** → activar **redis**.
2. Si no aparece: descargar DLL en [pecl.php.net/package/redis](https://pecl.php.net/package/redis) (misma versión PHP + thread safety que `php -i | findstr "Thread"`).
3. Copiar `php_redis.dll` a `C:\laragon\bin\php\php-{version}\ext\` y en `php.ini`: `extension=redis`.
4. Reiniciar Laragon.

### Predis (sin extensión)

```powershell
cd C:\laragon\www\VISERVICE
composer require predis/predis
```

`.env`: `REDIS_CLIENT=predis`

### Docker Desktop

[docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop/) → instalar → reiniciar PC si lo pide.

---

## Redis con Docker

```powershell
cd C:\laragon\www\VISERVICE
docker compose -f docker-compose.redis.yml up -d
docker exec -it viservice-redis redis-cli ping
```

Respuesta: `PONG`.

Parar: `docker compose -f docker-compose.redis.yml stop`

---

## `.env`

```env
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
CACHE_STORE=redis
```

Probar Laravel:

```powershell
php artisan config:clear
php artisan tinker --execute="Redis::ping();"
```
