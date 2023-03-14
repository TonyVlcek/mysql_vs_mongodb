# MySQL as NoSQL: Benchmarking MySQL 8.0 vs MongoDB

![MyMo logo](./docs/mymo_logo.png)

This project benchmarks the performance of MySQL 8.0 and MongoDB for inserting and querying JSON documents, aiming to answer whether MySQL can compete with MongoDB on the NoSQL playing field. The study was conducted on the Google Cloud Platform, and the results showed that MongoDB outperforms MySQL in all scenarios, with a larger difference in insertion times. This confirms the hypothesis that MongoDB will perform better since it is purpose-built for NoSQL operations. For write-heavy applications, MongoDB could provide benefits, while for applications where read operations are more prevalent, it may be worth sticking to MySQL. Limitations of this study include the lack of index optimization and the need for more variables. Future research could include a join operation and consider economic aspects of adding a NoSQL database next to an existing MySQL instance.

You can find detailed description fo the project and a discussion of the results in [this paper](./docs/MySQL_vs_MongoDB__paper.pdf).

## Quick start

1️⃣ Change variables in the `./toolbox.zsh` file then run:
```bash
source ./toolbox.zsh
```

If you are only going to run tests locally then you only need to specify the `PROJECT_ROOT='/path/to/project/root'` path. For deployment to GCP to work the other GCP (and setting up of a project in GCP) will be needed.

2️⃣ These commands will then be available in your terminal
```bash
mymo-up               # ⏩ to start containers
mymo-mongo-run-all    # 🍀 to run benchmark for MongoDB
mymo-mysql-run-all    # 🐬 to run benchmark for MySQL
mymo-cli              # 📋 to see all supported commands of the benchmarking client
mymo-down             # ☠️ to stop all containers

mymo-gcp-up           # 🚀 to deploy and run the experiment in GCP
mymo-gcp-down         # 🛬 to delete GCP resources and download results
```
