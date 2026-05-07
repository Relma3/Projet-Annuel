FROM golang:1.22-alpine AS builder

WORKDIR /app

COPY main.go .

RUN go mod init silverhappy-go-api && \
    go get github.com/go-sql-driver/mysql@v1.7.1 && \
    go mod tidy && \
    CGO_ENABLED=0 GOOS=linux go build -o go-api .

FROM alpine:latest

WORKDIR /app

COPY --from=builder /app/go-api .

EXPOSE 8080

CMD ["./go-api"]