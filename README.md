# IDEA Engine AI is a Pioneering Global Community

![image](assets/IDEA-Engine-Text-Logo-350x50.png)

This is the public repo for the IDEA Engine AI project!
<!-- OVERVIEW -->
## IDEA Engine AI Overview

<!-- FEATURES -->
## Features

- ✅ Text Generation
    - ✅ Connect to Open AI compatible API's i.e. LocalAI
    - ✅ Select different prompts
    - [x] Syntax highlighting for code
- [ ] Image Generation


<!-- ROADMAP -->
## Roadmap

- ✅ Text Generation
    - ✅ Connect to Open AI compatible API's i.e. LocalAI
    - ✅ Select different prompts
    - [x] Syntax highlighting for code
- [ ] Image Generation
    - 🟠 Connect to stable diffusion
- [x] Authentication
    - [x] Email/Password sign in and registration
    - [x] SSO
- [x] Teams
    - [x] Invite Team Members
    - [x] Manage the teams you belong to
    - [x] Create new teams
    - [x] Switch between teams
    - [x] RBAC
- [x] Document Management
    - [x] Document Upload
    - [x] Allow user to create datasets
    - [x] UI for datasets table 
    - [x] Turn documents into 1K batches and generate embeddings
    - [x] OCR for document upload
- [x] Document Pipelines
    - [x] Allow user to upload docs via API to datasets
    - [x] Process documents and create chunks and embeddings
- [x] Retrieval Augmented Generation
    - [x] Parse text out of documents
    - [x] Generate Embeddings and store in pgVector
    - [x] Add Embeddings to the prompt using similarity search
- [x] Prompt Management 
    - [x] Create and Edit prompts on a per team basis
    - [x] Associate prompts with datasets
- [x] Model Management 
    - [x] Create/update default prompt fo a model
    - [x] Set model location URL.
    - [x] Switchable LLM backends.
    - [ ] Associate models with a command i.e. /image
- [ ] Guardrails
    - [ ] Figure out a strategy
- [x] API
    - [x] Create per team API keys
    - [x] Attach keys to a prompt
    - [ ] Revoke keys
- [ ] Fine Tuning
    - [ ] QLORA adapters
- [x] System Admin
    - [x] Usage Statistics
    - [x] Audit Trail
    - [ ] Set API limits
- [x] Deployment
    - [x] Docker compose so people can test quickly.
    - [x] Kubernetes deployment strategy.
    - [x] Kubernetes Operator
    - [ ] Hardware recommendations.

## Git LFS
We use Git LFS for dependencies that are expensive to build.

To make sure you have everything you need to start building, you'll need to
install the `git-lfs` package for your favourite operating system, then run the
following commands in this repo:

``` PowerShell
    git lfs install
    git lfs pull
```
