# 13 — CI/CD Workflow

```mermaid
flowchart LR
    DEV[Developer commits on<br/>feature/* or sprint/* branch] --> PUSH[git push]
    PUSH --> PR[Pull Request → develop]
    PR --> CI{GitHub Actions<br/>.github/workflows/ci.yml}
    CI --> S1[composer install]
    S1 --> S2[vendor/bin/pint --test<br/>code style]
    S2 --> S3[php artisan test<br/>SQLite in-memory]
    S3 --> OK{All green?}
    OK -- No --> FIX[Fix and push again] --> PR
    OK -- Yes --> REV[Review by the other developer]
    REV --> MERGE[Merge into develop]
    MERGE --> REL[End of sprint: merge develop → main<br/>tag sprint-N / v1.0.0]
```

**Status:** the workflow file exists in the repository. It runs only after the repository is pushed to GitHub
(no remote configured yet — see `PROJECT_STATUS.md`). Deployment is manual (see Deployment Guide).
