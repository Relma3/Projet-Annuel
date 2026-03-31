package main

import (
	"encoding/json"
	"fmt"
	"net/http"
	"os"
)

func healthHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]string{
		"status":  "ok",
		"service": "silverhappy-go-api",
		"version": "1.0.0",
	})
}

func notificationsHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != "POST" {
		http.Error(w, "Methode non autorisee", http.StatusMethodNotAllowed)
		return
	}

	var data map[string]interface{}
	err := json.NewDecoder(r.Body).Decode(&data)

	if err != nil {
		http.Error(w, "JSON invalide", http.StatusBadRequest)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)

	json.NewEncoder(w).Encode(map[string]interface{}{
		"success": true,
		"message": "Notification envoyee",
	})
}

func statsHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]int{
		"total_seniors":      0,
		"total_prestataires": 0,
		"reservations_mois":  0,
	})
}

func main() {
	port := os.Getenv("GO_PORT")

	if port == "" {
		port = "8080"
	}

	http.HandleFunc("/health", healthHandler)
	http.HandleFunc("/api/notifications", notificationsHandler)
	http.HandleFunc("/api/stats", statsHandler)

	fmt.Println("Go micro-service lance sur le port " + port)
	http.ListenAndServe(":"+port, nil)
}
