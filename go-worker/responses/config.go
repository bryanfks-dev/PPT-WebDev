package responses

import (
	"models"
)

type ConfigResponse models.ConfigJson

func (config_response *ConfigResponse) Create(config_json models.ConfigJson) {
	config_response.CheckInTime = config_json.CheckInTime
	config_response.CheckOutTime = config_json.CheckOutTime
	config_response.AbsenceQuota = config_json.AbsenceQuota
	config_response.DailyWorkHours = config_json.DailyWorkHours
	config_response.WeeklyWorkHours = config_json.WeeklyWorkHours
}
