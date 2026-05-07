FROM golang:1.22-alpine

WORKDIR /app

COPY main.go .

RUN go mod init silverhappy-go-api && \
    go build -o go-api .

EXPOSE 8080

CMD ["./go-api"]