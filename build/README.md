# Docker Build Files

## Directory Structure

```
console
  - build
    - [image]
      - config/     # files to be mounted from host filesystem
      - files/      # files to be mounted during build (only if Dockerfile exists)
      - Dockerfile  # docker build file
```