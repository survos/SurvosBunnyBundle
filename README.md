# BunnyBundle

A Symfony bundle to interact with BunnyCDN via the [Bunny-PHP library](https://github.com/ToshY/BunnyNet-PHP).

Still under development, feedback welcome!  

## Quickstart
```bash
symfony new bunny-demo --webapp && cd bunny-demo
composer require survos/bunny-bundle
```


## Installation

Go to https://dash.bunny.net/account/api-key and get the main api key.  You should create at least one zone, as the bundle does not support creating zones.

Create a new Symfony project.

```bash
symfony new bunny-demo --webapp && cd bunny-demo
composer require survos/bunny-bundle
bin/console bunny:config <api-key> >> .env.local 
bin/console bunny:list
```

You can browse interactively with the basic admin controller.

```bash
composer require survos/simple-datatables-bundle
symfony open:local --path=/bunny
```

Or edit .env.local and add your API key.

As each storage zone has its own passwords and id, these need to be configured individually in survos_bunny.yaml.  Rather than tediously configuring each zone by cutting and pasting, we can use the first utility to dump the configuration with just the main api key.  This saves you from having to go to  https://dash.bunny.net/storage and go to each storage zone, then click on it and select "FTP and ApiAccess" and selecting each key.


```bin
bin/console bunny:config <api-key> 
```

Note: use --filter to limit to the zones to a regex (@todo)

You can skip passing the api key on the command line by defining it as an environment variable, etc.
```bash
echo "BUNNY_API_KEY=api-key >> .env.local
```

This command dumps the packages/config/survos_bunny.yaml file with references to the environment variables, which are also dumped and should be added to .env.local.  If your application only reads from bunny, you can remove the password environment variables, it is only used during writing.  You can also remove the main api key if your application doesn't need it in production.

Open .env.local and replace the values.

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

