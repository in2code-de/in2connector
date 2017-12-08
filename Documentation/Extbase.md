# in2connector as LDAP Backend for Extbase

You can configure in2connector to act as a storage backend for extbase fpór specific extbase models.
Everything you need is to configure your model in TypoScript, the rest of the implementation is default extbase.

Create your CRUD Controller, Model and Repository. Do not create TCA for your model.
Define your model's properties in ext_typoscript_setup.txt instea. Here is an example:

```
config.tx_extbase {
  persistence {
    classes {
      VerteXVaaR\Saxophon\Domain\Model\Person {
        mapping {
          tableName = saxophon|users
          columns {
            uid.mapOnProperty = uid
            pid.mapOnProperty = pid
            firstName.mapOnProperty = firstName
            lastName.mapOnProperty = lastName
            username.mapOnProperty = username
            loginShell.mapOnProperty = loginShell
            homeDirectory.mapOnProperty = homeDirectory
            fullName.mapOnProperty = fullName
            gid.mapOnProperty = gid
            news {
              config {
                type = inline
                foreign_table = tx_news_domain_model_news
                foreign_field = ldap_user
              }
            }
          }
        }
        ldap_mapping {
          rdnAttribute = cn
          columns {
            givenname = firstName
            sn = lastName
            cn = fullName
            uid = username
            loginshell = loginShell
            homedirectory = homeDirectory
            uidnumber = uid
            gidNumber = gid
          }
          objectClass {
            0 = top
            1 = posixAccount
            2 = person
            3 = organizationalPerson
            4 = inetOrgPerson
          }
        }
      }
    }
  }
}
```

The `ldap_mapping` part describes the name of a field in ldap and of the model.
The `rdnAttribute` is used to identify a record (the last part of the record's DN).
Also add all `objectClass`es new objects should have.
The `mapping` part is for extbase. The `tableName` contains the connection identifier of your connection demand:

ext_localconf.php:
```
$connectionRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(In2code\In2connector\Registry\ConnectionRegistry::class);
$connectionRegistry ->demandConnection('saxophon|users', TX_IN2CONNECTOR_DRIVER_LDAP);
```

(Do not forget to edit the connection and set the correct base DN for your user's directory (e.g. `dc=users,dc=example,dc=com`)


