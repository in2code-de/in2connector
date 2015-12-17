CATO
====

A CATO is a "Computing AND Transfer Object". It's purpose is to sort, filter and connect objects which belong together
as well as transfer these objects. It is intended to be used as a link between Controller and Views like DTOs, but with
some features.

CATOs and DTOs explained:

* A DTO MUST not have any method. It's solely purpose is to transfer data.
* A CATO MAY implement logic needed to do one or more of these tasks:
  * Sort the data
  * Filter the data
  * Transfer the data
  * Connect the data together
* Neither a CATO nor a DTO may alter the encapsulated data.

The CATO contains the logic or uses another object for the required task(s).

About CATO properties:

* A CATO MUST have at least one property referencing an object or multiple objects. These are called "storage properties". They MUST obey following rules:
  * Storage properties MUST have following annotation ``@cato\storage``
  * Storage properties MUST be publicly immutable once defined. (Set via or in constructor, no public setter)
  * Storage properties MAY contain the result of the computation.
  * Storage properties MUST contain only values of type ``object`` or ``object[]``. [1]_
* Additional properties are OPTIONAL. They are called "buffer properties". They MUST obey ALL of the following rules:
  * Any buffer property MUST exclusively reference values from the storage properties.
  * They MUST have following annotation ``@cato\buffer``
  * They MUST not be publicly writable (by visibility or setter)
* Buffer properties SHOULD be altered, set or unset by protected methods.

CATOs in general:

* A CATO MUST only be used where the logic does not apply to any of the given objects.
* A CATO MUST reside under the Domain/Model/CATO folder.
* The computation SHOULD be delayed to the latest possible point. [Lazyness]
* Results are expected to be static (they don't need to be computed again, because the result SHOULD NOT change). [Caching]
* The computing method SHOULD be called ``compute`` an SHOULD be at least protected.
* It SHOULD NOT be possible to force the CATO to recompute the result if it is static.
* A CATO MUST be silent (not void). It MUST NOT throw exceptions but it is RECOMMENDED to log errors.
* A CATO MUST always return the expected and annotated type.

This pattern was devised by Oliver Eglseder at 2015-12-17.

.. [1] Arrays may also be objects implementing ``Countable``, ``Iterator`` AND ``ArrayAccess``.
