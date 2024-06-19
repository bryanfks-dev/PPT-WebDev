package main

import (
	"fmt"
	"models"
	"net/http"

	"apis"
	"auths"
	"configs"
	"db"
)

var (
	mux           = http.NewServeMux()
	server_config = configs.Server{}
	db_config     = configs.Database{}
	jwt_config    = configs.JWT{}
)

func initEndPoints() {
	// Login endpoints
	mux.Handle("/auth/verify-token",
		auths.AuthenticationMiddlware(http.HandlerFunc(auths.VerifyToken)))
	mux.Handle("/auth/user/login",
		auths.AuthenticationMiddlware(http.HandlerFunc(auths.UserLoginHandler)))
	mux.Handle("/auth/admin/login",
		auths.AuthenticationMiddlware(http.HandlerFunc(auths.AdminLoginHandler)))

	// User account related endpoints
	mux.Handle("/api/users/me/profile",
		auths.AuthenticationMiddlware(
			auths.UserMiddleware(http.HandlerFunc(apis.GetUserProfileHandler))))
	mux.Handle("/api/user/motivation",
		auths.AuthenticationMiddlware(
			auths.UserMiddleware(http.HandlerFunc(apis.GetUserMotivation))))
	mux.Handle("/api/user/attend",
		auths.AuthenticationMiddlware(
			auths.UserMiddleware(http.HandlerFunc(apis.AttendUserHandler))))
	mux.Handle("/api/users/me/attendance",
		auths.AuthenticationMiddlware(
			auths.UserMiddleware(http.HandlerFunc(apis.GetUserAttendanceHandler))))
	mux.Handle("/api/users/me/attendance/today",
		auths.AuthenticationMiddlware(
			auths.UserMiddleware(http.HandlerFunc(apis.GetUserAttendanceTodayHandler))))
	mux.Handle("/api/users/me/attendance/stats",
		auths.AuthenticationMiddlware(
			auths.UserMiddleware(http.HandlerFunc(apis.GetUserAttendanceStatsHandler))))

	// Admin users endpoints
	mux.Handle("/api/users",
		auths.AuthenticationMiddlware(
			auths.AdminMiddleware(http.HandlerFunc(apis.GetUsersHandler))))
	mux.Handle("/api/users/create",
		auths.AuthenticationMiddlware(
			auths.AdminMiddleware(http.HandlerFunc(apis.CreateUserHandler))))
	mux.Handle("/api/users/update/{id}",
		auths.AuthenticationMiddlware(
			auths.AdminMiddleware(http.HandlerFunc(apis.UpdateUserHandler))))
	mux.Handle("/api/users/delete/{id}",
		auths.AuthenticationMiddlware(
			auths.AdminMiddleware(http.HandlerFunc(apis.DeleteUserHandler))))
	mux.Handle("/api/users/search/{query}",
		auths.AuthenticationMiddlware(
			auths.AdminMiddleware(http.HandlerFunc(apis.SearchUserHandler))))

	// Department endpoints
	mux.Handle("/api/departments",
		auths.AuthenticationMiddlware(
			auths.AdminMiddleware(http.HandlerFunc(apis.GetDepartmentsHandler))))
	mux.Handle("/api/departments/create",
		auths.AuthenticationMiddlware(
			auths.AdminMiddleware(http.HandlerFunc(apis.CreateDepartmentHandler))))
	mux.Handle("/api/departments/update/{id}",
		auths.AuthenticationMiddlware(
			auths.AdminMiddleware(http.HandlerFunc(apis.UpdateDepartmentHandler))))
	mux.Handle("/api/departments/delete/{id}",
		auths.AuthenticationMiddlware(
			auths.AdminMiddleware(http.HandlerFunc(apis.DeleteDepartmentHandler))))
	mux.Handle("/api/departments/search/{query}",
		auths.AuthenticationMiddlware(
			auths.AdminMiddleware(http.HandlerFunc(apis.SearchDepartmentHandler))))

	// Config endpoints
	mux.Handle("/api/configs",
		auths.AuthenticationMiddlware(http.HandlerFunc(apis.GetConfigsHandler)))
	mux.Handle("/api/configs/save",
		auths.AuthenticationMiddlware(
			auths.AdminMiddleware(http.HandlerFunc(apis.SaveConfigsHandler))))

	// Department users Endpoints
	mux.Handle("/api/users/me/department/users",
		auths.AuthenticationMiddlware(
			auths.UserMiddleware(http.HandlerFunc(apis.GetDepartmentUsersHandler))))
	mux.Handle("/api/users/me/department/users/update/{id}",
		auths.AuthenticationMiddlware(
			auths.UserMiddleware(auths.ManagerMiddleware(
				http.HandlerFunc(apis.UpdateDeparmentUserHandler)))))

	// Jobs endpoints
}

func loadConfig(config configs.ConfigInterfaces) {
	err := config.Load()

	// Ensure no error load config
	if err != nil {
		panic(err.Error())
	}
}

func main() {
	loadConfig(&server_config)
	loadConfig(&db_config)
	loadConfig(&jwt_config)

	// Assign secret key
	models.JWT_Secret = []byte(jwt_config.Secret)

	// Connect to database
	err :=
		db.Connect(db_config.User, db_config.Password,
			db_config.Host, db_config.Port, db_config.Database)

	// Ensure no error connecting to database
	if err != nil {
		panic(err.Error())
	}

	fmt.Printf("Connected to database %s:%s (%s)\n", db_config.Host, db_config.Port, db_config.Database)

	initEndPoints()

	fmt.Printf("API Server is running on http://%s:%s\n", server_config.Host, server_config.Port)
	fmt.Println("Logs:")

	// Open server connection
	err = http.ListenAndServe(server_config.Host+":"+server_config.Port, mux)

	// Ensure no error
	if err != nil {
		panic(err.Error())
	}

	defer db.Conn.Close()
}
