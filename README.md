# BunnyBundle

Under development...

## Installation

Create a new project

```bash
symfony new bunny-demo --webapp && cd bunny-demo
```

Until the recipe is property working, set the apiKey and readonlyPassword in your environment, the .env.local file during development and in the secrets value or however you set your environment in production.

Go to https://dash.bunny.net/account/api-key to get the main api key

Go to https://dash.bunny.net/storage and create a storage zone, then click on it and select "FTP and ApiAccess".  Copy those keys.

At the moment, the bundle only works with a single storage zone.  

```bash
cat << 'END' > .env.local
BUNNY_API_KEY="xxxxxxx-b025-42c7-b5f9-de009bfb5b7ea7a89fd3-2182-4868-zzzz-zzzzzzzz"
BUNNY_PASSWORD=aaaaaaa-bbbb-ccccc-9805984a87bb-8379-4aed
BUNNY_READONLY_PASSWORD=aaaaaaa-bbbb-ccccc-9805984a87bb-8379-4aed
END
```

Open .env.local and replace the values.

```bash
composer require survos/bunny-bundle
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

