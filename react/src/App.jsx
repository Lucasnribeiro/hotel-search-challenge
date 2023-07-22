import { useQuery } from "@tanstack/react-query";
import React, { useEffect, useState } from "react";
import Spinner from "./components/spinner";

function App() {
  const [latitude, setLatitude]     = useState('');
  const [longitude, setLongitude]   = useState('');
  const [orderBy, setOrderBy]       = useState('proximity');
  const [error, setError]           = useState(false)

  useEffect(() => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(success, error);
    } else {
      console.log('Geolocation not supported');
    }

    function success(position) {
      const userLatitude = position.coords.latitude;
      const userLongitude = position.coords.longitude;
      console.log(`Latitude: ${userLatitude}, Longitude: ${userLongitude}`);
      setError(false)
      setLatitude(userLatitude);
      setLongitude(userLongitude);
    }

    function error() {
      setError(true)
      console.log('Unable to retrieve your location');
    }
  }, []); // Empty dependency array to run the effect only once

  const { data, isLoading, isFetching } = useQuery(['hotels', latitude, longitude, orderBy], async () => {

    const url = `http://hotel-search-env-1.eba-ckhwursa.us-east-1.elasticbeanstalk.com/?latitude=${latitude}&longitude=${longitude}&orderby=${orderBy}`;

    const response = await fetch(url);

    if (!response.ok) {
      throw new Error('Request Error');
    }

    return response.json();
  },
  {
    enabled: latitude != ''
  }
  
  );

  const handleSubmit = (e) => {
    e.preventDefault();

    setLatitude(e.target.latitude.value);
    setLongitude(e.target.longitude.value);
    setOrderBy(e.target.orderby.value);
  };

  return (
    <div class="min-h-screen flex items-center justify-center">
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-5">
        <div class="shadow-lg  text-gray-600 text-lg font-bold text-center p-10 rounded-lg col-span-4">Hotel Search</div>
        <form onSubmit={handleSubmit} class="bg-green-100  text-lg font-bold text-center p-10 rounded-lg col-span-full">

            <div className="flex flex-col space-y-4">
              <label>
                Latitude
                <input defaultValue={latitude} name="latitude" type="text" placeholder="Latitude" className="p-2 rounded-md"></input>
              </label> 
              <label>
                Longitude
                <input defaultValue={longitude} name="longitude" type="text" placeholder="Longitude" className="p-2 rounded-md"></input>
              </label>
              <select name="orderby" className="text-slate-400">
                <option value='proximity'>Proximity</option>
                <option value='pricepernight'>Price / night</option>
              </select>
            </div>
            <div>
              <button className="mt-6 border-2 py-2 px-4 bg-slate-200 hover:bg-slate-300 rounded-md" type="submit">Search</button>
            </div>
            {
              error && 
              <div className="mt-10">
                Geolocation not available, please insert coordinate values
              </div> 
            }
        </form>
      </div>
      
      <div class="shadow-lg text-gray-600 text-lg font-bold text-center p-10 rounded-lg col-span-8 max-h-64 overflow-y-scroll">
        { isFetching ?
          <Spinner />
          :
          data?.map(hotel => 
            <>
              <div className="flex flex-col justify-start items-start my-2">
                <h1 className="text-gray-700">{hotel.name} <span className="text-sm text-gray-500">{hotel.distance.toFixed(2)} km</span></h1>
                <h3 className="text-sm"> Price per Night: <span className="text-sm text-gray-500">${hotel.price}</span></h3>
              </div>
              <hr/>
            </>
            
          ) 
        }
      </div>
    </div>
  )
}

export default App
