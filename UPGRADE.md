From v3 to v4
=============

Nothing really changed. You need to **type** everywhere.
- DomainRuleInterface - meaning, every "rule" of your app is impacted for methods `::on()` `::after()` `::execute()`
- DomainEvent (if you override any methods of this class)

From v2 to v3
=============

Configuration
------

### Before

```yaml
biig_domain:
    # Default to true
    override_doctrine_instantiator: true
    # Act on default if none specified
    entity_managers: []
    # Were disabled if not specified
    persist_listeners:
        doctrine: ['default']
```

### After

ℹ️ No configuration is required, if the bundle is installed, all its features will be enabled!

```yaml
biig_domain:
    # You can specify on which entity_manager the bundle is active
    # Defaults to empty array []
    entity_managers: ['default']
```
