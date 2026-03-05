# Cake

**A topographical version control system.**

Cake is an experimental alternative to stream-based version control. It is not a better Git. It is a different model entirely.

---

## The Problem

Stream-based version control systems share three structural flaws that no amount of tooling can fix, because they are consequences of the model itself.

**They assume all code is eventually mergeable.** This is a workflow assumption embedded as an architectural truth. It is wrong for a meaningful class of projects and teams.

**Merging is destructive.** When a branch is merged, the act produces a new state that cannot be cleanly decomposed back into its constituents. The further back you need to go, the more expensive and error-prone that becomes. There is no clean way to carry forward only a specific set of changes from a prior point in history. Cherry-pick is a crude workaround, not a solution.

**There are no named, bounded units of work.** Once changes enter the stream, they are deltas. Not things. The stream model has no concept of a coherent unit of work that retains its identity through composition.

These flaws are not unique to Git. They are characteristic of the stream model, and most version control systems share them.

---

## The Model

Cake replaces the stream with a topographical model.

The fundamental unit is a **layer**: an immutable, content-addressed snapshot of only the files that were touched. Not the entire codebase. Just the touched files.

Layers are composed into **recipes**: named, ordered definitions of what a particular version of a project contains. A recipe is baked to produce output. Baking is deterministic, non-destructive, and repeatable. The layers are never consumed by the process that uses them.

The result is that a release is not a snapshot of merged, irreducible streams. It is a precise composition of named, scoped units of work. Adding a feature to a customer means adding a layer to their recipe. Removing it means removing that layer. No feature flags. No conditional code paths. No collateral damage.

The model also naturally promotes atomic CI/CD pipelines, locally scoped pull requests, and disciplined change management — not through policy, but through incentive structure.

---

## Status

Cake is theoretical. It may not result in a viable tool. The goal at this stage is to validate the model, not ship a product.

Cake is being developed using Cake. Git is used as a backup during alpha development. If the model cannot manage its own codebase cleanly, the theory has not been proven.

---

## Read More

The full Technical Design Document is in [`TDD.md`](./TDD.md). It covers the model in more detail, the design philosophy, resolved design questions, and the criteria to consider the theory proven or falsified.

---

## Get Involved

This is an early, experimental project and feedback from people who think seriously about version control, software architecture, and developer tooling is genuinely valuable. If the model interests or infuriates you, both reactions are welcome.

Open an issue, start a discussion, or reach out directly.