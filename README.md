![image](https://github.com/App-Abacus-Limited/IDEA-Engine-AI/assets/31509362/100ba664-7c45-42bf-bdb6-d044ce6af075)# IDEA Engine AI is a Pioneering Global Community
This is the public repo for the IDEA Engine AI project!

![image](assets/IDEA-Engine-Text-Logo-350x50.png)
<!-- OVERVIEW -->
## Overview
The platform is a project that is powered by the underlying microservices:

- IDEA Engine - Community Portal
	- IDEA Engine - IDEA Profile Managment
	- IDEA Engine - IDEA Content Managment	
	- IDEA Engine - IDEA Project Managment	
	- IDEA Engine - Personal AI Companion
- IDEA Engine - Internal Development Solutions (DevOps)
  	- IDEA Engine - Model Factory
	- IDEA Engine - Coding Factory
	- IDEA Engine - DevOps Project Management
- UniSphere - Universal Data Lake
  	- IDEA Engine - Surreal DB
  	- IDEA Engine - Qdrant
- Actcentive - Loyalty Coins

The IDEA Engine stands as a beacon for global knowledge and prosperity enhancement. It's not just a platform; it's a movement that categorizes its vast community into three distinct segments: individuals, communities, and businesses.

At its core, the IDEA Engine empowers every member. Whether you're nurturing a fresh idea or advancing an existing project, the platform's AI-driven technology is designed to propel you forward, regardless of whether the project is personal, communal, or business-oriented.

Central to the IDEA Engine is the Universal Data Lake using Azure, Microsoft Fabric, Semantic Kernel including a comprehensive repository using Microsoft Graph connecting individual, communities and businesses. This seamlessly integrates with the Actcentive brand e-commerce marketplace, a dynamic space that harnesses the power of affiliate marketing, social media, and more to generate revenue. While Actcentive remains an integral part of this ecosystem, further details on its operation and benefits will be elaborated upon in subsequent sections.

The platform's heartbeat is its array of Language Learning Models (LLMs). These AI models, tailored for specific platform workflows, draw inspiration from cutting-edge research projects. The spectrum of models spans from renowned ones like OpenAI's GPT to open-source solutions and proprietary models developed using ML.NET. The software architecture is based on a number of opensource projects Microsoft AutoGen, ChatDev, Aider, 

Every project on the IDEA Engine is unique, and the platform acknowledges this. Depending on project specifics such as geographical location, category (individual, community, business), and sector, it undergoes a tailored workflow, ensuring optimal results.

Powering the IDEA Engine is a robust technological foundation:

	• Cloud and AI Services: Microsoft Azure, Azure OpenAI, Azure Machine Learning
	• Azure Kubernetes microservice architecture using Docker containers  
	• Databases: Surreal Db, Qdrant, Azure Redis
	• Web Services: Azure web app service (Linux, NGINX)
	• Development Frameworks: Rust, Python, JavaScript, NodeJS, TypeScript, ASP. NET CORE 8.0, .NET MAUI .NET 8.0, .NET Blazor, .NET 8.0, ML.NET, Microsoft Semantic Kernel, Microsoft CoPilot's, OpenAI, Stability AI
	• Programming Languages: Rust, Python, C#, NodeJS, React, Ruby
	• AI Libraries: Huggingface, Langchain
	• Collaboration Tools: GitHub, GitHub Copilot, GitHub Copilot Chat
	• IDEs: Visual Studio 2022, VS Code

This list, while extensive, is just the tip of the iceberg. The IDEA Engine is an amalgamation of these technologies, each playing a pivotal role in shaping the platform.

The platform's initial development phase zeroes in on the IDEA Engine Coder project. Drawing inspiration from a plethora of open-source projects on GitHub, the objective is clear: forge a groundbreaking platform using the synergies of Microsoft MAUI, Blazor, C#, Python, and Azure. The future of development is here, and it's powered by the IDEA Engine.![image](https://github.com/App-Abacus-Limited/IDEA-Engine-AI/assets/31509362/f2d356bb-860a-4e7e-b467-d6d0c9ca5bea)

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
## Open Source Project
A list of the open source projects GitHub repos being used as inspiration:

The overall platform will be driven by workflows based on the Open Project (GitHub repo link below) and this will be merged and updated to suit the platforms requirements. Essentially each IDEA being managed by the platform will be regarded as a project and managed accordioning.

https://github.com/App-Abacus-Limited/openproject

Microsoft Semantic Kernel will be used to manage the AI planner, agent, persona and other aspects of the AI LLM infrastructure:
https://github.com/microsoft/semantic-kernel

https://github.com/microsoft/chat-copilot

https://github.com/microsoft/semantic-memory

https://github.com/Azure-Samples/miyagi

Development tools to inspire the IDEA Engine coder project. The goal is to develop an end to end development project lifecycle solution that uses both Visual Studio, VSCode and the IDEA Engines UI to manage the coding aspect of a project. These are a few of the open source projects that will be used to build the overall IDEA Engine Coder solution:



https://github.com/paul-gauthier/aider

 https://github.com/biobootloader/mentat

https://github.com/AntonOsika/gpt-engineer

https://github.com/smol-ai/developer

https://github.com/kuafuai/DevOpsGPT

https://github.com/sourcegraph/cody

https://github.com/sourcegraph/sourcegraph

Image generation:

https://github.com/Stability-AI/StableStudio

https://github.com/Stability-AI/stablediffusion


LLM acceleration 
Based on the vLLM project which will be integrated into this platform (GitHub repo link below):

https://github.com/App-Abacus-Limited/vllm

Platform Agents
The platforms community membership aspect includes individual personalised models that are allowed to communicate as agents with each other throughout the community. This concept requires additional research and details to be defined. The overall concept is based on the Generative Agent concept (GitHub rep link below):

https://github.com/joonspk-research/generative_agents

https://github.com/TransformerOptimus/SuperAGI

Additional inspiration and concepts sourced from the MetaGPT project:

https://github.com/App-Abacus-Limited/MetaGPT

Other concepts that will be included in the overall IDEA Engine platform solution:
https://github.com/ShishirPatil/gorilla

https://github.com/mshumer/gpt-author

https://github.com/kyegomez/tree-of-thoughts

https://github.com/App-Abacus-Limited/langflow

https://github.com/trrahul/llama2.cs

https://github.com/pinokiocomputer/pinokio

https://github.com/AIAnytime/Llama2-Medical-Chatbot

https://github.com/karpathy/nanoGPT

https://github.com/SamurAIGPT/EmbedAI

https://github.com/PromtEngineer/localGPT

https://github.com/pinokiocomputer/pinokiod

https://github.com/App-Abacus-Limited/ChatALL

https://github.com/App-Abacus-Limited/FastChat

https://github.com/App-Abacus-Limited/AgentGPT

https://github.com/App-Abacus-Limited/SalesGPT

https://github.com/App-Abacus-Limited/researchgpt

https://github.com/App-Abacus-Limited/quivr

https://github.com/homanp/superagent

https://github.com/assafelovic/gpt-researcher

https://github.com/alexpunct/chatgpt-journal

https://github.com/huggingface/chat-ui

https://github.com/jamesmontemagno/ThreadsApp

Documentation:

https://github.com/MicrosoftDocs/semantic-kernel-docs


Distributed Networks:
https://github.com/bigscience-workshop/petals

