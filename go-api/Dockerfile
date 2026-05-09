FROM golang:1.22-alpine

WORKDIR /app

COPY main.go .

RUN go mod init silverhappy-go-api && \
    go get github.com/go-sql-driver/mysql@v1.7.1 && \
    go build -o go-api .

EXPOSE 8080

CMD ["./go-api"]