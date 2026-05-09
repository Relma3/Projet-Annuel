package main

import (
	"encoding/json"
	"fmt"
	"net/http"
	"os"
)

type HealthResponse struct {
	Status  string `json:"status"`
	Service string `json:"service"`
	Version string `json:"version"`
}

type NotificationRequest struct {
	UserID  int    `json:"user_id"`
	Message string `json:"message"`
	Type    string `json:"type"`
}

func healthHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(HealthResponse{
		Status:  "ok",
		Service: "silverhappy-go-api",
		Version: "1.0.0",
	})
}

func notificationsHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}
	var req NotificationRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "Invalid JSON", http.StatusBadRequest)
		return
	}
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"success": true,
		"message": fmt.Sprintf("Notification envoyée à user %d : %s", req.UserID, req.Message),
	})
}

func statsHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
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
	fmt.Printf("Go micro-service Silver Happy démarré sur le port %s\n", port)
	http.ListenAndServe(":"+port, nil)
}
