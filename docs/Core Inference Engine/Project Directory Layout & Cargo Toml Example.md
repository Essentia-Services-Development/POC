# Project Directory Layout & Cargo Toml Example

This will be reviewed and updated

```css
project_name/
│
└───src/
    │   main.rs
    │
    ├───lib/
    │   │   index.rs
    │   │
    │   ├───model_serving/
    │   │      mod.rs
    │   │
    │   ├───llm/
    │   │      mod.rs
    │   │
    │   ├───slm/
    │   │      mod.rs
    │   │
    │   └───lam/
    │          mod.rs
    │
    └───text_processing/
        │   mod.rs
        │
        ├───text_embedding/
        │      mod.rs
        │
        └───qdrant_interface/
              mod.rs
              qdrant_client.rs
```

### Dependencies in `Cargo.toml`

Replace `project_name` with your actual project name and include the below dependencies in your `Cargo.toml` file. Note that I removed unnecessary fields like edition and replaced default\_features with true to avoid worrying about missing features.

```toml
[package]
name = "project_name"
version = "0.1.0"

[dependencies]
# Hugging Face dependencies
text-generation-inference = "*"
llm-ls = "*"
text-embeddings-inference = "*"
tokenizers = "*"
candle = { git = "https://github.com/huggingface/candle.git", branch = "main" }

# Other dependencies
burn = { git = "https://github.com/tracel-ai/burn.git", branch = "main" }
bloop = { git = "https://github.com/BloopAI/bloop.git", branch = "main" }
bionic-gpt = { git = "https://github.com/bionic-gpt/bionic-gpt.git", branch = "main" }
qdrant = { git = "https://github.com/QdrantHQ/qdrant-rs.git", branch = "master" }
serde = { version = "1.0", features = ["derive"] }
serde_json = "1.0"
tokio = { version = "1.13.0", features = ["full"] }
futures = "0.3.5"
```

After creating the project folder, navigate to it in your terminal and initialize the project with `cargo init`, followed by updating the `Cargo.toml` dependencies. Next, organize your code within the proposed layout. Finally, don't forget to register an account on [crates.io](https://crates.io/) if you haven't done so, and upload your project once completed to share and collaborate with others. Good luck with your project!
