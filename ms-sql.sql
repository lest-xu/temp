/****** Object:  UserDefinedFunction [dbo].[fn_ConvertToLocalDateTime]    Script Date: 2/4/2020 9:46:59 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

/***********************************************************************************************************
*   Name:                   [fn_ConvertToLocalDateTime]
*   Parameters:
*   Purpose:                [fn_ConvertToLocalDateTime]
*   Created By:             Lester Xu
*   Created Date:           Feb 03, 2020
*
***********************************************************************************************************/
ALTER FUNCTION [dbo].[fn_ConvertToLocalDateTime] 
(
	@Date  DateTime 
)
RETURNS NVARCHAR(36)
AS
	BEGIN
	DECLARE @LocalDateTime DateTime

	SELECT @LocalDateTime =  CONVERT(datetime, SWITCHOFFSET(@Date, DATEPART(TZOFFSET, @Date AT TIME ZONE 'Eastern Standard Time')))

	RETURN (@LocalDateTime)
END


/***********************************************************************************************************
*get data by last hour
***********************************************************************************************************/
SELECT TOP (1000) [ID]
      ,[Name]
      ,[Percentage]
      ,[Value]
      ,[CTCMPAONumbers]
      ,[Details]
      ,[CreatedAt]
  FROM [dbo].[TV_AnnualRenewalStatusEveryHour]
  where
CAST([CreatedAt] AS DATETIME)   > DATEADD(HOUR, -1,  [dbo].[fn_ConvertToLocalDateTime] ( GETDATE() ) )

