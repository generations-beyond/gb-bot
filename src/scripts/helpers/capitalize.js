export default function capitalize(str = false, delimiter = ' ') {
    if( !str || typeof str !== 'string' )
        return
    const arr = str.split(delimiter)
    for (var i = 0; i < arr.length; i++) {
        arr[i] = arr[i].charAt(0).toUpperCase() + arr[i].slice(1)
    }
    return arr.join(" ")
}
