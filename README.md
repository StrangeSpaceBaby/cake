# cake

An experimental proof-of-concept verson control system using a topographical mentla model.

1. All operations are either insert or remove
2. Layer-based, not branch-based
3. Layers can be infinitely composed
4. Layers cannot be changed after being added to a recipe
5. Always resolve forward (removes merge bottlenecks)
6. Explicit conflict resolution
7. Never lose work
