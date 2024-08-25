# BunnyBundle

Under development...

## Installation

Until the recipe is property working, set the apiKey and readonlyPassword in your environment, the .env.local file during development and in the secrets value or however you set your environment in production.


```bash
cat << 'END'
BUNNY_API_KEY="xxxxxxx-b025-42c7-b5f9-de009bfb5b7ea7a89fd3-2182-4868-zzzz-zzzzzzzz"
BUNNY_READONLY_PASSWORD=aaaaaaa-bbbb-ccccc-9805984a87bb-8379-4aed
END'

composer install survos/bunny-bundle
```

Your application now has a bare-bones controller located at /admin/bunny, you may want to secure this route in security.yaml, or configure it in config/routes/survos_bunny.yaml.

You also have access to a command line interface.

```bash
bin/console bunny:list
```

```bash
+------------- museado/ -----+--------+
| ObjectName     | Path      | Length |
+----------------+-----------+--------+
| photos finales | /museado/ | 0      |
+----------------+-----------+--------+


```

