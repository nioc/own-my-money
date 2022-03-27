import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import localizedFormat from 'dayjs/plugin/localizedFormat'
import duration from 'dayjs/plugin/duration'
import advancedFormat from 'dayjs/plugin/advancedFormat'
import localeData from 'dayjs/plugin/localeData'
import isoWeek from 'dayjs/plugin/isoWeek'
import 'dayjs/locale/fr'

dayjs.locale('fr')
dayjs.extend(relativeTime)
dayjs.extend(localizedFormat)
dayjs.extend(duration)
dayjs.extend(advancedFormat)
dayjs.extend(localeData)
dayjs.extend(isoWeek)

export default dayjs