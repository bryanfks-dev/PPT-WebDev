package apis

import (
	"auths"
	"database/sql"
	"encoding/json"
	"log"
	"net/http"
	"strconv"
	"strings"
	"sync"

	"db"
	"forms"
	"models"
	"responses"

	"github.com/golang-jwt/jwt/v5"
	"golang.org/x/crypto/bcrypt"
)

var (
	postMu sync.Mutex
)

func GetUsersHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method == http.MethodGet {
		postMu.Lock()
		defer postMu.Unlock()

		// Set HTTP header
		w.Header().Set("Content-Type", "application/json")

		users, err := models.User{}.Get()

		// Ensure no error fetching user data
		if err != nil {
			log.Panic("Error get user: ", err.Error())

			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "server error",
			})

			return
		}

		var response_data []responses.UserResponse

		for _, user := range users {
			var user_data responses.UserResponse

			err := user_data.Create(user)

			if err != nil {
				w.WriteHeader(http.StatusInternalServerError)
				json.NewEncoder(w).Encode(map[string]any{
					"error": "server error",
				})

				return
			}

			// Prepare response
			response_data = append(response_data, user_data)
		}

		w.WriteHeader(http.StatusOK)
		json.NewEncoder(w).Encode(map[string]any{
			"data": response_data,
		})
	}
}

func CreateUserHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method == http.MethodPost {
		postMu.Lock()
		defer postMu.Unlock()

		// Set HTTP header
		w.Header().Set("Content-Type", "application/json")

		// Decode json to struct
		req_json := json.NewDecoder(r.Body)

		var user_form forms.UserForm

		err := req_json.Decode(&user_form)

		if err != nil {
			w.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "bad request",
			})

			return
		}

		user_form.Sanitize()

		valid, err := user_form.ValidateCreate()

		// Ensure no error validating form
		if !valid {
			if err != forms.ErrInvalidEmail && err != forms.ErrEmailExist &&
				err != forms.ErrInvalidPhoneNumber && err != forms.ErrInvalidNIK {
				w.WriteHeader(http.StatusInternalServerError)
				json.NewEncoder(w).Encode(map[string]any{
					"error": "server error",
				})

				return
			}

			w.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(w).Encode(map[string]any{
				"error": err.Error(),
			})

			return
		}

		// Prepare new user field
		user := models.User{
			FullName:     user_form.FullName,
			Email:        user_form.Email,
			Password:     user_form.PhoneNumber,
			PhoneNumber:  user_form.PhoneNumber,
			DateOfBirth:  user_form.BirthDate,
			Address:      user_form.Address,
			NIK:          user_form.NIK,
			Gender:       user_form.Gender,
			DepartmentId: user_form.DepartmentId,
			Photo:        user_form.Photo,
		}

		id, err := user.Insert()

		// Assign inserted id into current field
		user.Id = id

		// Ensure no error when inserting user
		if err != nil {
			log.Panic("Error insert user", err.Error())

			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "server error",
			})

			return
		}

		err = models.Salary{
			UserId:  id,
			Initial: 0,
			Current: 0,
		}.Insert()

		// Ensure no error when inserting salary
		if err != nil {
			log.Panic("Error insert salary", err.Error())

			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "server error",
			})

			return
		}

		log.Printf("New user `%s` has been created", user.FullName)

		var response_data responses.UserResponse

		err = response_data.Create(user)

		// Ensure no error creating response
		if err != nil {
			log.Panic("Error create response", err.Error())

			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "server error",
			})

			return
		}

		w.WriteHeader(http.StatusCreated)
		json.NewEncoder(w).Encode(map[string]any{
			"data": response_data,
		})
	}
}

func UpdateUserHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method == http.MethodPut {
		postMu.Lock()
		defer postMu.Unlock()

		// Set HTTP header
		w.Header().Set("Content-Type", "application/json")

		// Retrieve value from url
		id, err := strconv.Atoi(r.PathValue("id"))

		// Ensure user provide a valid record id
		if err != nil || id <= 0 {
			w.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "Invalid user id",
			})

			return
		}

		_, err = models.User{}.GetUsingId(id)

		// Ensure no error fetching user using id
		if err != nil {
			if err == sql.ErrNoRows {
				w.WriteHeader(http.StatusBadRequest)
				json.NewEncoder(w).Encode(map[string]any{
					"error": "Invalid user id",
				})

				return
			}

			log.Panic("Error get user", err)

			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "server error",
			})

			return
		}

		// Decode json to struct
		req_json := json.NewDecoder(r.Body)

		var user_form forms.UserForm

		err = req_json.Decode(&user_form)

		if err != nil {
			w.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "bad request",
			})
		}

		valid, err := user_form.ValidateUpdate(id)

		// Ensure no error validating form
		if !valid {
			if err != forms.ErrInvalidEmail && err != forms.ErrEmailExist &&
				err != forms.ErrInvalidPhoneNumber && err != forms.ErrInvalidNIK {
				w.WriteHeader(http.StatusInternalServerError)
				json.NewEncoder(w).Encode(map[string]any{
					"error": "server error",
				})

				return
			}

			w.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(w).Encode(map[string]any{
				"error": err.Error(),
			})

			return
		}

		// Database transaction
		tx, err := db.Conn.Begin()

		// Ensure no error starting database transaction
		if err != nil {
			log.Panic("Error starting database transaction", err.Error())

			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "server error",
			})

			return
		}

		defer tx.Rollback()

		user, err := models.User{}.GetUsingId(id)

		// Ensure no error when fetching data
		if err != nil {
			log.Panic("Error get user", err.Error())

			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "server error",
			})

			return
		}

		// Update records
		user.FullName = user_form.FullName
		user.Email = user_form.Email
		user.DateOfBirth = user_form.BirthDate
		user.Address = user_form.Address
		user.NIK = user_form.NIK
		user.Gender = user_form.Gender
		user.PhoneNumber = user_form.PhoneNumber
		user.DepartmentId = user_form.DepartmentId

		// Try update user credentials
		if strings.TrimSpace(user_form.NewPassword) != "" {
			hashed_pwd, err := bcrypt.GenerateFromPassword([]byte(user_form.NewPassword), 11)

			// Ensure no error hasing new password
			if err != nil {
				log.Panic("Error hashing password", err.Error())

				w.WriteHeader(http.StatusInternalServerError)
				json.NewEncoder(w).Encode(map[string]any{
					"error": "server error",
				})

				return
			}

			user.Password = string(hashed_pwd)
		}

		err = user.Update()

		// Ensure no error when updating user
		if err != nil {
			log.Panic("Error update user", err.Error())

			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "server error",
			})

			return
		}

		// Ensure no error commiting to database
		if err := tx.Commit(); err != nil {
			log.Panic("Error commiting to database", err.Error())

			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "server error",
			})

			return
		}

		log.Printf("User `%s` record has been updated\n", user.FullName)

		w.WriteHeader(http.StatusOK)
		json.NewEncoder(w).Encode(map[string]any{
			"data": user,
		})
	}
}

func DeleteUserHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method == http.MethodDelete {
		postMu.Lock()
		defer postMu.Unlock()

		// Set HTTP header
		w.Header().Set("Content-Type", "application/json")

		// Retrieve value from url
		id, err := strconv.Atoi(r.PathValue("id"))

		// Ensure user provide a valid record id
		if err != nil || id <= 0 {
			w.WriteHeader(http.StatusBadRequest)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "bad request",
			})

			return
		}

		user, err := models.User{}.GetUsingId(id)

		// Ensure no error get user data
		if err != nil {
			log.Panic("Error get user", err.Error())

			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "server error",
			})

			return
		}

		err = user.Delete()

		// Ensure no error when deleting data
		if err != nil {
			log.Panic("Error delete user", err.Error())

			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "server error",
			})

			return
		}

		log.Println("User", user.FullName, "deleted")

		w.WriteHeader(http.StatusOK)
		json.NewEncoder(w).Encode(map[string]any{
			"data": map[string]any{
				"id": id,
			},
		})
	}
}

func GetUserProfileHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method == http.MethodGet {
		postMu.Lock()
		defer postMu.Unlock()

		// Set HTTP header
		w.Header().Set("Content-Type", "application/json")

		token := r.Context().Value(auths.TOKEN_KEY).(jwt.MapClaims)

		user, err :=
			models.User{}.GetUsingId(int(token["id"].(float64)))

		// Ensure no error when getting user information
		if err != nil {
			if err == sql.ErrNoRows {
				w.WriteHeader(http.StatusBadRequest)
				json.NewEncoder(w).Encode(map[string]any{
					"error": "invalid user",
				})

				return
			}

			log.Panic("Error get user: ", err.Error())

			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "server error",
			})

			return
		}

		var response_data responses.UserResponse

		err = response_data.Create(user)

		// Ensure no error create response
		if err != nil {
			log.Panic("Error create response: ", err.Error())

			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]any{
				"error": "server error",
			})
		}

		w.WriteHeader(http.StatusOK)
		json.NewEncoder(w).Encode(map[string]any{
			"data": response_data,
		})
	}
}