# Cake

Topographical Version Control
Technical Design Document
Version 0.6 — DRAFT


## Problem & Approach
Stream-based version control systems (of which Git is the most prevalent example) share a set of structural flaws that are not addressable through better tooling or improved interfaces. They are consequences of the underlying model.

The first flaw is an assumption of eventual mergeability. Stream-based systems treat all code as ultimately destined to merge with all other code. This is a workflow assumption embedded as an architectural truth, and it is wrong for a meaningful class of projects and teams. Not all code belongs together. Not all branches are temporary.

The second flaw is that merging is a destructive operation. When a branch is merged, it becomes part of the main line, ceasing to exist as a distinct, composable entity. More precisely, the act of merging produces a new state that cannot be cleanly decomposed back into its constituents. The merged result is irreducible. Unwinding it means replaying the entire stream from a prior point, and cherry-picking forward means operating on raw deltas with no semantic boundaries to guide you. The further back you go, the more expensive and error-prone that operation becomes. In Cake, bake output is produced separately from the layers that define it. The layers are never consumed by the process that uses them. The output is disposable. The ingredients are permanent.

The third flaw is the absence of named, bounded units of work that survive composition. The closest operation most stream-based systems offer for selective forward-porting of changes is cherry-pick, a crude tool that operates on individual commits rather than semantic units of work. Any developer who has attempted to go back to a point in a repository's history and carry forward only a specific set of changes knows how quickly this breaks down. If the unit of work spans multiple commits, or if those commits are interleaved with unrelated changes, the stream model offers no clean solution. This is not a tooling gap. It is a structural limitation: once changes enter the stream, they are deltas, not things.

These flaws are not unique to Git. They are characteristic of the stream model itself, and most version control systems in widespread use share them.

### Approach
Cake is a response to the model, not to any particular implementation of it. Its goal is not to build a better stream-based tool. It is to replace the stream model with one in which units of work retain their identity, composition is non-destructive, and selective forward-porting is a first-class operation.

As a consequence of this model, Cake naturally promotes more atomic CI/CD pipelines, more locally scoped pull requests, and more disciplined change management, not through policy enforcement, but through incentive structure. For example, a layer representing a Jira ticket contains exactly the controllers, services, and migrations that ticket requires. The scope is defined by the work, not by the mechanics of the tool.

### Practical Example
Consider a Laravel application initialized with Cake. The first layer is the base Laravel installation, the clean, unmodified foundation every subsequent layer builds on. A developer begins work on user registration. They create a layer called new-user.layer, write their controllers, services, and migrations, and commit the layer to the server. That layer contains only and exactly the files they touched. Nothing else. The base layer already exists. Consumers of this layer don't need to have the entire codebase shipped to them. They compose it themselves.

The developer working on OAuth creates oauth.layer on top of a recipe that includes the base and new-user.layer. They work against a fully assembled codebase locally, but their layer contains only their changes. A QA team wanting to spin up an isolated test environment takes those layers, adds their own instrumentation layer, bakes the recipe, and gets a clean, precise environment containing exactly what they need.

Now a product manager decides that a premium authentication feature should be shipped to specific enterprise customers. The dev team has already built auth-premium.layer. Each enterprise customer has a recipe. Delivering the feature to a customer means adding auth-premium.layer to their recipe at the correct composition point and baking a new release. Removing it, rolling it back, or substituting a different implementation means modifying one line in one recipe. No feature flags. No conditional code paths. No shipping bugs and limitations that belong to other customers.

This is where the topographical model earns its value. A release is not a snapshot of merged, irreducible streams. With Cake, a recipe defines the order and resolutions for each layer so that subsequent bakes can reproduce the exact same output in different contexts. Without violating layer immutability, and thus preserving forward interoperability.

## API Design Philosophy

### Model Clarity Over Feature Completeness
Every design decision in Cake is subordinate to the clarity of its conceptual model. A feature that is useful but that requires the user to think in terms of the implementation (rather than the model) is a design failure, not a success. Cake would rather do less and be understood completely than do more and require a manual to reason about. To that end, Cake exposes an API and is designed to be chainable with Unix processes and other scripting contexts, so that anything beyond Cake's core mission can be addressed through custom or open source tooling.

### No Silent Automation of Judgment Calls
Version control tools that automate merge conflict resolution introduce a category of error that is particularly dangerous: silent, plausible-looking errors that pass automated checks and surface only at runtime or in production. Cake takes the position that any operation that cannot be resolved deterministically without developer judgment must surface that judgment call explicitly. Automation is appropriate for mechanical tasks. Humans are appropriate for decisions.

### Immutability as a First Principle
History in Cake is immutable. Once a layer exists, it cannot be modified. This is not a constraint imposed for technical reasons. It is a philosophical commitment. A version control system that allows the rewriting of history is one in which history cannot be trusted. Cake trades the flexibility of mutable history for the reliability of a permanent record.

## The Topographical Model
Stream-based version control sees code as an ever-forward arrow: new work merged into a top layer, history trailing behind it, the present state defined by everything that came before. Cake sees code differently. Code is unitized work that can be reconstituted, recomposed, and reused as technological or market opportunities arise. The present state is not the product of history. It is a deliberate composition of named, bounded units.

The metaphor is a layered cake. If you have a recipe for a vanilla cake and you love it, you can make it over and over. It is reproducible by definition. Now someone hands you a recipe for a neapolitan cake with chocolate, strawberry, and vanilla layers. You make the batters separately, flavor them separately, and bake them separately. But for the vanilla layer, you use your own recipe, because you know it works. You substitute your layer into their recipe. The result is a new cake that contains your work, intact, exactly as you made it.

This is the topographical model. Layers stack. Areas of the codebase with more changes have more touched files and form peaks. Code becomes isolated, portable, and composable. The same layer can appear in any number of recipes without being altered by any of them.

### Layers
A layer is Cake's fundamental unit of versioned state. It is an immutable, named snapshot of a set of files at a point in time, identified by a hash of its contents. A layer does not record what changed relative to a previous state. It records what exists. The relationship between layers is compositional, not sequential.

A layer is created deliberately by the developer when a meaningful, stable state has been reached. It is not created automatically on every save or commit operation. This distinction is intentional: Cake layers represent intentional checkpoints, not an exhaustive log of incremental activity.

### Recipes
A recipe is a named, ordered composition of layers. It defines which layers (and in what arrangement) constitute a particular version of the project. Recipes are the mechanism by which Cake expresses concepts that Git expresses through branches: a distinct, named line of work that combines specific contributions into a coherent whole.

### Baking
Baking is the process of resolving a recipe into concrete output such as a working directory, a release archive, a deployed artifact. To bake a recipe is to take its constituent layers, apply them in the defined order, and produce a final, usable state. The baking process is deterministic: the same recipe, with the same layers, always produces the same output.

Baking does not modify any layer. It is a read-only operation on the layer store, producing a new artifact. The output of a bake is not itself a layer unless the developer explicitly promotes it to one.

### The Kitchen
The kitchen is the Cake equivalent of a repository. It is the root context within which layers, recipes, and configuration are managed. A kitchen is initialized once and defines the scope of a Cake-managed project. The kitchen contains the layer store, the recipe definitions, and the kitchen configuration. It does not contain working files. Those exist in the working directory, which the kitchen observes but does not own.

### Executables
A recipe entry is not strictly a reference to a layer. If an entry points to a file and that file is executable, Cake runs it at bake time in sequence rather than composing it. This makes the recipe the single definition of an entire pipeline: some entries compose code, some entries execute processes, all in a defined and reproducible order.

Executable entries are first-class citizens of the recipe. They can themselves be wrapped in a layer, making them versioned, distributable, and composable like any other artifact. A build script, a test runner, a deployment hook: any of these can be a layer on the server, pulled into a recipe, and run as part of a bake. The implication for CI/CD is substantial. The pipeline is not a separate system bolted onto version control. It is expressed directly in the recipe, using the same model as everything else.


## Implementation Goals & Non-Goals

### Goals
Cake aims to
1. provide a version control model that a developer can fully understand from first principles without reading complicated documentation.
2. make the relationship between working files, versioned history, and release output explicit and traceable.
3. surface developer judgment calls rather than resolve them automatically.
4. produce deterministic, reproducible outputs from defined inputs.
5. validate its own design through dogfooding: Cake versioned using Cake during development. (Git will be used as a backup during alpha development)

### Non-Goals
Cake does NOT aim to
1. replicate Git's feature set.
2. interoperate with Git repositories, import Git history, or serve as a drop-in replacement.
3. be the right tool for every project or every team.
4. optimize for the workflows of large-scale distributed development teams, as those workflows are shaped by constraints that Cake's current scope does not address.
5. provide a graphical interface.

#### POC Implementation Tech Stack
Cake is a CLI tool because this most easily satisfies the requirement to expose an API and allow for tooling integrations. The server will also provide a lean API for managing kitchens via integrated tooling. Both the CLI and the server are written in PHP. Cake is currently theoretical and may not result in a viable tool. In this, the implementation language is mostly irrelevant so long as the theory can be investigated and assessed.

## Theory Validation Criteria
Cake is currently theoretical. The following criteria define the minimum conditions under which the topographical model can be considered proven. These are not shipping milestones. They are falsifiable tests. If any criterion cannot be satisfied, the theory requires revision.

1. **Kitchen initialization** A kitchen can be initialized on an existing codebase without disrupting it. The working directory is observed, not owned.

1. **Layer creation** A developer can create a layer from a set of touched files. The layer contains only and exactly those files. The layer is identified by a content hash and is immutable after creation.

1. **Layer composition** Two or more layers can be composed into a recipe. The recipe can be baked to produce a complete, functional output. The same recipe baked twice produces identical output.

1. **Conflict resolution** When two layers in a recipe modify the same file, deterministic changes are applied and reported. Non-deterministic conflicts are surfaced interactively on the CLI and require explicit developer resolution before the bake can complete.

1. **Layer promotion** The output of a bake can be promoted to a new layer. That layer is subject to the same immutability rules as any other.

Layer deletion. A developer can explicitly delete a layer. Deletion is permanent and does not affect any recipe that no longer references it.

Server distribution. Layers can be pushed to and pulled from a server endpoint. A pulled layer becomes an independent local artifact. The server and the local kitchen remain decoupled after the pull.

Executable recipe entries. A recipe entry pointing to an executable file runs that file at bake time in sequence. The bake does not proceed past a failed executable entry.

Dogfooding. Cake can be used to version control its own development. Git will be used as a backup during alpha development. If the model cannot manage its own codebase cleanly, the theory has not been proven.
Further Clarifications
Conflict Semantics
Conflicts are handled uniformly regardless of where they arise. When two layers in a recipe modify the same file, the files are diffed at bake time. Changes that can be merged deterministically are applied and reported to the user for transparency. Changes that cannot be merged deterministically (genuine overlapping modifications) are surfaced interactively on the CLI for explicit developer resolution, in the manner of SVN. There is no automatic resolution of ambiguous conflicts. This policy is universal and applies consistently across all contexts in which a conflict can arise.
Layer Granularity
A layer is a mental construct representing a set of changes. Cake imposes no semantic constraints on what a layer should represent. Granularity is entirely at developer discretion. The practical ceiling on granularity is a UI constraint: a CLI that cannot efficiently browse, search, and compare large numbers of layers will naturally push developers toward coarser, more meaningful layers. This is intentional.

Layers are flexible enough to represent the developer's or the team's thinking. Undisciplined minds will produce chaotic layer structures. Cake does not attempt to prevent this. Teams may also compose multiple layers into a single promoted layer (effectively a localized squash), consolidating work without rewriting history. The original constituent layers remain intact unless explicitly deleted.

Explicit, deliberate deletion of superseded layers by the developer is supported. This is compatible with Cake's immutability principle: immutability means layers cannot be modified, not that they must be kept forever. Deletion is a conscious act, never automatic.
Multi-Contributor Model
A kitchen is a shared working context, but sharing a kitchen does not imply overlapping work. Contributors operating in distinct areas of the codebase produce independent layers whose composition at the recipe level is trivial. Conflicts only arise when two contributors' layers touch the same files.

The topographical model rewards good code organization directly. Teams that structure their codebase into well-bounded modules will find that their layers compose cleanly and their recipes are straightforward to build. Teams that do not will find that bake-time conflicts are frequent and painful. Cake does not enforce modularity, but it makes the cost of ignoring it visible and concrete. Crucially, this discipline serves the developers, not a process, not a methodology, not a scrum master. The reward for good organization is reduced cognitive load and cleaner composition. It is intrinsically motivated, not externally imposed.

Layer distribution between contributors is handled via a server endpoint. The server exposes a list of available layers, with text search or equivalent discovery. Developers pull layers from the server into their local kitchen. A pulled layer is not a reference to the server's copy. It becomes an independent local artifact, subject to the same rules as any locally created layer. The server is a distribution mechanism, not a source of truth that local kitchens stay synchronized with.
Partial Bakes
This is not a special operation. A developer wishing to bake a subset of a recipe's layers defines a recipe containing that subset, bakes it, and optionally promotes the output to a new layer. The existing mechanism covers this case entirely.


Terminology
Kitchen: The root context of a Cake-managed project. Analogous to a repository in Git.

Layer: An immutable, content-addressed snapshot of a set of files. The fundamental unit of versioned state in Cake.

Recipe: A named, ordered composition of layers that defines a particular version of a project.

Bake: The process of resolving a recipe into a concrete output by applying its layers in order.

Chef: The CLI entry point for Cake commands.

Working directory: The live filesystem state observed by the kitchen. Not owned by Cake. Observed by it.

Executable entry: A recipe entry that points to an executable file rather than a layer. Run in sequence at bake time as part of the composition process.


Document History
v0.1 — Initial draft. Theory and philosophy only. Implementation details deferred.
v0.2 — Resolved all open design questions: conflict semantics, layer granularity, multi-contributor model, partial bakes.
v0.3 — Problem statement rewritten. Removed Git-centric framing. Articulated three structural flaws of the stream model and emergent workflow benefits of the topographical model.
v0.4 — Added practical example subsection to problem statement.
v0.5 — Added section 3.5 on executable recipe entries and updated terminology.
v0.6 — Added theory validation criteria section.

